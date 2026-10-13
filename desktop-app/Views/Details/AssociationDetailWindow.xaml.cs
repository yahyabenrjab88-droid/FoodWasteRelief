using System;
using System.Windows;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Details;

public partial class AssociationDetailWindow : Window
{
    private readonly int _id;

    public AssociationDetailWindow(int id)
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
            const string sql = "SELECT nom, ville, telephone, status FROM associations WHERE id=@id LIMIT 1";
            await using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@id", _id);
            await using var r = await cmd.ExecuteReaderAsync();
            if (!await r.ReadAsync())
            {
                Close();
                return;
            }

            NameText.Text = Convert.ToString(r["nom"]) ?? "";
            VilleText.Text = Convert.ToString(r["ville"]) ?? "";
            PhoneText.Text = Convert.ToString(r["telephone"]) ?? "";
            StatusBadge.Status = Convert.ToString(r["status"]) ?? "";
            Title = $"Détail association — {NameText.Text}";
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
            Close();
        }
    }

    private void Close_Click(object sender, RoutedEventArgs e) => Close();
}
