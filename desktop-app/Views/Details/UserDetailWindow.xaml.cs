using System;
using System.Windows;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Details;

public partial class UserDetailWindow : Window
{
    private readonly int _userId;
    public UserDetailWindow(int id) { _userId = id; InitializeComponent(); Loaded += LoadedAsync; }
    private async void LoadedAsync(object sender, RoutedEventArgs e)
    {
        try
        {
            await using var conn = await DatabaseService.GetConnectionAsync();
            const string sql = @"SELECT CONCAT(u.prenom,' ',u.nom) AS full_name, u.email, u.role, COALESCE(u.status,'actif') AS user_status, u.created_at,
                                        b.nom_business, b.localisation, b.description,
                                        a.nom AS association_nom, a.ville AS association_ville, a.telephone AS association_phone
                                 FROM users u LEFT JOIN business b ON b.user_id=u.id LEFT JOIN associations a ON a.user_id=u.id
                                 WHERE u.id=@id LIMIT 1";
            await using var cmd = new MySqlCommand(sql, conn); cmd.Parameters.AddWithValue("@id", _userId);
            await using var r = await cmd.ExecuteReaderAsync(); if (!await r.ReadAsync()) { Close(); return; }
            FullNameText.Text = Convert.ToString(r["full_name"]) ?? ""; EmailText.Text = Convert.ToString(r["email"]) ?? "";
            RoleBadge.Status = Convert.ToString(r["role"]) ?? ""; StatusBadge.Status = Convert.ToString(r["user_status"]) ?? "";
            CreatedText.Text = Convert.ToDateTime(r["created_at"]).ToString("dd/MM/yyyy");
            var role = (Convert.ToString(r["role"]) ?? "").ToLowerInvariant();
            if (role == "business" && r["nom_business"] is not DBNull)
            { ExtraLine1Text.Text = $"Établissement: {Convert.ToString(r["nom_business"])} | Adresse: {Convert.ToString(r["localisation"]) ?? "—"}"; ExtraLine2Text.Text = $"Description: {Convert.ToString(r["description"]) ?? "—"}"; ExtraLine1Text.Visibility = Visibility.Visible; ExtraLine2Text.Visibility = Visibility.Visible; }
            if (role == "association" && r["association_nom"] is not DBNull)
            { ExtraLine1Text.Text = $"Association: {Convert.ToString(r["association_nom"])} | Ville: {Convert.ToString(r["association_ville"]) ?? "—"}"; ExtraLine2Text.Text = $"Téléphone: {Convert.ToString(r["association_phone"]) ?? "—"}"; ExtraLine1Text.Visibility = Visibility.Visible; ExtraLine2Text.Visibility = Visibility.Visible; }
        }
        catch (Exception ex) { MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning); Close(); }
    }
    private void Close_Click(object sender, RoutedEventArgs e) => Close();
}
