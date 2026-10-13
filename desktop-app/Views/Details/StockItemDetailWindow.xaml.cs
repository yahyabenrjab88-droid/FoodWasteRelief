using System;
using System.Collections.Generic;
using System.Windows;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Details;

public partial class StockItemDetailWindow : Window
{
    private readonly int _id;
    public StockItemDetailWindow(int id)
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
            const string sql = "SELECT item_name, quantity, unit, expiry_date, added_at FROM storage_items WHERE id=@id LIMIT 1";
            await using (var cmd = new MySqlCommand(sql, conn))
            {
                cmd.Parameters.AddWithValue("@id", _id);
                await using var r = await cmd.ExecuteReaderAsync();
                if (!await r.ReadAsync()) { Close(); return; }
                NameText.Text = Convert.ToString(r["item_name"]) ?? "";
                var unit = Convert.ToString(r["unit"]) ?? "";
                QtyText.Text = $"{Convert.ToDecimal(r["quantity"]):F2} {unit}";
                ExpText.Text = r["expiry_date"] is DBNull ? "—" : Convert.ToDateTime(r["expiry_date"]).ToString("dd/MM/yyyy");
                AddedText.Text = Convert.ToDateTime(r["added_at"]).ToString("dd/MM/yyyy HH:mm");
                Title = $"Détail stock — {NameText.Text}";
                await r.CloseAsync();
            }

            const string pSql = "SELECT nom, quantite FROM produits WHERE storage_item_id=@id ORDER BY id DESC";
            await using (var pCmd = new MySqlCommand(pSql, conn))
            {
                pCmd.Parameters.AddWithValue("@id", _id);
                await using var r = await pCmd.ExecuteReaderAsync();
                var list = new List<dynamic>();
                while (await r.ReadAsync()) list.Add(new { Nom = Convert.ToString(r["nom"]) ?? "", Quantite = Convert.ToInt32(r["quantite"]) });
                ProductsGrid.ItemsSource = list;
                await r.CloseAsync();
            }

            const string dSql = @"SELECT a.nom AS association_nom, d.quantite, d.status FROM donations d JOIN associations a ON a.id=d.association_id WHERE d.storage_item_id=@id ORDER BY d.id DESC";
            await using var dCmd = new MySqlCommand(dSql, conn);
            dCmd.Parameters.AddWithValue("@id", _id);
            await using var dr = await dCmd.ExecuteReaderAsync();
            var dlist = new List<dynamic>();
            while (await dr.ReadAsync()) dlist.Add(new { Association = Convert.ToString(dr["association_nom"]) ?? "", Quantite = Convert.ToInt32(dr["quantite"]), Status = Convert.ToString(dr["status"]) ?? "" });
            DonationsGrid.ItemsSource = dlist;
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
            Close();
        }
    }

    private void Close_Click(object sender, RoutedEventArgs e) => Close();
}
