using System;
using System.Windows;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Details;

public partial class DonationDetailWindow : Window
{
    private readonly int _id;
    private readonly DonationService _service = new();
    public bool Changed { get; private set; }

    public DonationDetailWindow(int id)
    {
        _id = id;
        InitializeComponent();
        Loaded += LoadedAsync;
    }

    private async void LoadedAsync(object sender, RoutedEventArgs e)
    {
        try
        {
            await using var conn = await DatabaseService.GetConnectionAsync();
            const string sql = @"SELECT d.id, d.quantite, d.status, d.date_donation, d.collected_at,
                                        COALESCE(p.nom, si.item_name, '') AS article_nom,
                                        COALESCE(si.unit, '') AS unit_name,
                                        a.nom AS association_nom,
                                        b.nom_business
                                 FROM donations d
                                 JOIN associations a ON a.id=d.association_id
                                 LEFT JOIN produits p ON p.id=d.produit_id
                                 LEFT JOIN storage_items si ON si.id=d.storage_item_id
                                 JOIN business b ON b.id = COALESCE(p.business_id, si.business_id)
                                 WHERE d.id=@id LIMIT 1";
            await using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@id", _id);
            await using var r = await cmd.ExecuteReaderAsync();
            if (!await r.ReadAsync()) { Close(); return; }
            BusinessText.Text = Convert.ToString(r["nom_business"]) ?? "";
            ItemText.Text = Convert.ToString(r["article_nom"]) ?? "";
            AssociationText.Text = Convert.ToString(r["association_nom"]) ?? "";
            var unit = Convert.ToString(r["unit_name"]) ?? "";
            QtyText.Text = unit.Length == 0 ? $"{Convert.ToInt32(r["quantite"]):F2}" : $"{Convert.ToInt32(r["quantite"]):F2} {unit}";
            StatusBadge.Status = Convert.ToString(r["status"]) ?? "";
            CreatedText.Text = Convert.ToDateTime(r["date_donation"]).ToString("dd/MM/yyyy HH:mm");
            CollectedText.Text = r["collected_at"] is DBNull ? "—" : Convert.ToDateTime(r["collected_at"]).ToString("dd/MM/yyyy HH:mm");
            Title = $"Détail don — {ItemText.Text}";
            CollectBtn.Visibility = Session.IsAssociation && string.Equals(Convert.ToString(r["status"]), "Accepted", StringComparison.OrdinalIgnoreCase)
                ? Visibility.Visible : Visibility.Collapsed;
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
            Close();
        }
    }

    private async void CollectBtn_Click(object sender, RoutedEventArgs e)
    {
        if (!Session.LinkedAssociationId.HasValue)
        {
            ToastHelper.Error("Dons", "Association non liee a la session.");
            return;
        }

        CollectBtn.IsEnabled = false;
        try
        {
            var ok = await _service.MarkCollectedByAssociationAsync(_id, Session.LinkedAssociationId.Value);
            if (ok)
            {
                Changed = true;
                Close();
            }
            else
            {
                ToastHelper.Error("Dons", "Impossible de marquer collecté.");
            }
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Dons", ex.Message);
        }
        finally
        {
            CollectBtn.IsEnabled = true;
        }
    }

    private void Close_Click(object sender, RoutedEventArgs e) => Close();
}
