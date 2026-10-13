using System;
using System.Collections.Generic;
using System.Windows;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Details;

public partial class OrderDetailWindow : Window
{
    private readonly int _id;
    private readonly OrderService _orders = new();
    public bool Changed { get; private set; }

    public OrderDetailWindow(int id)
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
            const string sql = @"SELECT c.status, c.date_commande, c.total,
                                        CONCAT(u.prenom,' ',u.nom) AS client_name,
                                        b.nom_business
                                 FROM commandes c
                                 JOIN users u ON u.id=c.client_id
                                 JOIN business b ON b.id=c.business_id
                                 WHERE c.id=@id LIMIT 1";
            await using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@id", _id);
            await using var r = await cmd.ExecuteReaderAsync();
            if (!await r.ReadAsync()) { Close(); return; }
            var status = Convert.ToString(r["status"]) ?? "en_attente";
            ClientText.Text = Convert.ToString(r["client_name"]) ?? "";
            BusinessText.Text = Convert.ToString(r["nom_business"]) ?? "";
            DateText.Text = Convert.ToDateTime(r["date_commande"]).ToString("dd/MM/yyyy HH:mm");
            StatusBadge.Status = OrderStatusHelper.ToFrenchLabel(status);
            TotalText.Text = $"Total: {Convert.ToDecimal(r["total"]):F2} TND";
            Title = $"Détail commande — {BusinessText.Text}";

            ConfirmBtn.Visibility = Session.IsBusiness && OrderStatusHelper.Normalize(status) == OrderStatusHelper.EnAttente ? Visibility.Visible : Visibility.Collapsed;
            DeliverBtn.Visibility = Session.IsBusiness && OrderStatusHelper.Normalize(status) == OrderStatusHelper.Confirmee ? Visibility.Visible : Visibility.Collapsed;
            CancelBtn.Visibility = Session.IsBusiness && (OrderStatusHelper.Normalize(status) == OrderStatusHelper.EnAttente || OrderStatusHelper.Normalize(status) == OrderStatusHelper.Confirmee) ? Visibility.Visible : Visibility.Collapsed;
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
            Close();
            return;
        }

        try
        {
            await using var conn2 = await DatabaseService.GetConnectionAsync();
            const string linesSql = @"SELECT p.nom AS produit_nom, cd.quantite, cd.prix_unitaire
                                  FROM commandes_details cd
                                  JOIN produits p ON p.id=cd.produit_id
                                  WHERE cd.commande_id=@id";
            await using var cmd2 = new MySqlCommand(linesSql, conn2);
            cmd2.Parameters.AddWithValue("@id", _id);
            await using var r2 = await cmd2.ExecuteReaderAsync();
            var list = new List<CommandeDetail>();
            while (await r2.ReadAsync())
                list.Add(new CommandeDetail { ProduitNom = Convert.ToString(r2["produit_nom"]) ?? "", Quantite = Convert.ToInt32(r2["quantite"]), PrixUnitaire = Convert.ToDecimal(r2["prix_unitaire"]) });
            LinesGrid.ItemsSource = list;
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
            Close();
        }
    }

    private async System.Threading.Tasks.Task UpdateAsync(string status)
    {
        try
        {
            await _orders.UpdateStatusAsync(_id, status, Session.LinkedBusinessId, Session.IsAdmin);
            Changed = true;
            Close();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private async void ConfirmBtn_Click(object sender, RoutedEventArgs e) => await UpdateAsync(OrderStatusHelper.Confirmee);
    private async void DeliverBtn_Click(object sender, RoutedEventArgs e) => await UpdateAsync(OrderStatusHelper.Livree);
    private async void CancelBtn_Click(object sender, RoutedEventArgs e) => await UpdateAsync(OrderStatusHelper.Annulee);
    private void Close_Click(object sender, RoutedEventArgs e) => Close();
}
