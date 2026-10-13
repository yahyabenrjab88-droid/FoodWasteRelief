using System;
using System.Windows;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Details;

public partial class ProductDetailWindow : Window
{
    private readonly int _id;
    private readonly CartService _cart = new();
    private int _available;
    private int _picked = 1;
    public bool AddedToCart { get; private set; }

    public ProductDetailWindow(int id)
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
            const string sql = @"SELECT p.nom, p.description, p.categorie, p.prix, p.quantite, p.date_expiration, b.nom_business
                                 FROM produits p JOIN business b ON b.id=p.business_id WHERE p.id=@id LIMIT 1";
            await using var cmd = new MySqlCommand(sql, conn);
            cmd.Parameters.AddWithValue("@id", _id);
            await using var r = await cmd.ExecuteReaderAsync();
            if (!await r.ReadAsync()) { Close(); return; }
            NameText.Text = Convert.ToString(r["nom"]) ?? "";
            BusinessText.Text = Convert.ToString(r["nom_business"]) ?? "";
            CategoryBadge.Status = Convert.ToString(r["categorie"]) ?? "";
            PriceText.Text = $"{Convert.ToDecimal(r["prix"]):F2} TND";
            _available = Convert.ToInt32(r["quantite"]);
            QtyText.Text = _available.ToString("F2");
            ExpText.Text = Convert.ToDateTime(r["date_expiration"]).ToString("dd/MM/yyyy");
            DescText.Text = Convert.ToString(r["description"]) ?? "";
            QtyPickText.Text = _picked.ToString();
            Title = $"Détail produit — {NameText.Text}";
            if (Session.IsClient)
                AddToCartBtn.Visibility = Visibility.Visible;
            else
                AddToCartBtn.Visibility = Visibility.Collapsed;
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
            Close();
        }
    }

    private void Minus_Click(object sender, RoutedEventArgs e)
    {
        if (_picked <= 1) return;
        _picked--; QtyPickText.Text = _picked.ToString();
    }

    private void Plus_Click(object sender, RoutedEventArgs e)
    {
        if (_picked >= Math.Max(1, _available)) return;
        _picked++; QtyPickText.Text = _picked.ToString();
    }

    private async void AddToCartBtn_Click(object sender, RoutedEventArgs e)
    {
        if (!Session.IsClient || Session.CurrentUser is null) return;
        try
        {
            await _cart.AddOrUpdateQuantityAsync(Session.CurrentUser.Id, _id, _picked);
            AddedToCart = true;
            Close();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Panier", ex.Message);
        }
    }

    private void Close_Click(object sender, RoutedEventArgs e) => Close();
}
