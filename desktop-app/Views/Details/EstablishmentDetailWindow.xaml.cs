using System;
using System.Windows;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Details;

public partial class EstablishmentDetailWindow : Window
{
    private readonly int _businessId;
    public EstablishmentDetailWindow(int id) { _businessId = id; InitializeComponent(); Loaded += LoadedAsync; }
    private async void LoadedAsync(object sender, RoutedEventArgs e)
    {
        try
        {
            await using var conn = await DatabaseService.GetConnectionAsync();
            const string sql = @"SELECT b.nom_business, b.localisation, b.description, b.status, b.created_at, CONCAT(u.prenom,' ',u.nom) AS owner_name FROM business b LEFT JOIN users u ON u.id=b.user_id WHERE b.id=@id LIMIT 1";
            await using var cmd = new MySqlCommand(sql, conn); cmd.Parameters.AddWithValue("@id", _businessId);
            await using var r = await cmd.ExecuteReaderAsync(); if (!await r.ReadAsync()) { Close(); return; }
            NameText.Text = Convert.ToString(r["nom_business"]) ?? ""; OwnerText.Text = Convert.ToString(r["owner_name"]) ?? "";
            AddressText.Text = Convert.ToString(r["localisation"]) ?? "—"; DescText.Text = Convert.ToString(r["description"]) ?? "—";
            StatusBadge.Status = Convert.ToString(r["status"]) ?? ""; CreatedText.Text = r["created_at"] is DBNull ? "—" : Convert.ToDateTime(r["created_at"]).ToString("dd/MM/yyyy");
        }
        catch (Exception ex) { MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning); Close(); }
    }
    private void Close_Click(object sender, RoutedEventArgs e) => Close();
}
