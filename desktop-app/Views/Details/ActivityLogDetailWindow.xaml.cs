using System;
using System.Windows;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Details;

public partial class ActivityLogDetailWindow : Window
{
    private readonly int _id;
    public ActivityLogDetailWindow(int id) { _id = id; InitializeComponent(); Loaded += LoadedAsync; }
    private async void LoadedAsync(object sender, RoutedEventArgs e)
    {
        try
        {
            await using var conn = await DatabaseService.GetConnectionAsync();
            const string sql = "SELECT username, user_role, action, details, created_at FROM activity_logs WHERE id=@id LIMIT 1";
            await using var cmd = new MySqlCommand(sql, conn); cmd.Parameters.AddWithValue("@id", _id);
            await using var r = await cmd.ExecuteReaderAsync(); if (!await r.ReadAsync()) { Close(); return; }
            UsernameText.Text = Convert.ToString(r["username"]) ?? ""; RoleBadge.Status = Convert.ToString(r["user_role"]) ?? "";
            ActionText.Text = Convert.ToString(r["action"]) ?? ""; DetailsText.Text = Convert.ToString(r["details"]) ?? "";
            DateText.Text = Convert.ToDateTime(r["created_at"]).ToString("dd/MM/yyyy HH:mm");
        }
        catch (Exception ex) { MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning); Close(); }
    }
    private void Close_Click(object sender, RoutedEventArgs e) => Close();
}
