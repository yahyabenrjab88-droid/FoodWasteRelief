using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Media;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using FoodWasteRelief.Views.Details;

namespace FoodWasteRelief.Views.Pages;

public partial class ClientCatalogPage : Page
{
    private readonly ProductService _products = new();
    private readonly CartService _cart = new();
    private List<Produit> _source = new();

    public ClientCatalogPage()
    {
        InitializeComponent();
        Loaded += async (_, _) => await LoadAsync();
    }

    private async Task LoadAsync()
    {
        CatalogErrorBanner.Visibility = Visibility.Collapsed;
        try
        {
            _source = await _products.GetCatalogForClientAsync();
            FillCategoryFilter();
            ApplyFilters();
        }
        catch (Exception ex)
        {
            CatalogErrorText.Text = ex.Message;
            CatalogErrorBanner.Visibility = Visibility.Visible;
        }
    }

    private void FillCategoryFilter()
    {
        var selected = (CategoryFilterBox.SelectedItem as ComboBoxItem)?.Content?.ToString();
        CategoryFilterBox.Items.Clear();
        CategoryFilterBox.Items.Add(new ComboBoxItem { Content = "Toutes les catégories", Tag = "", IsSelected = true });
        foreach (var c in _source.Select(p => p.Categorie).Where(s => !string.IsNullOrWhiteSpace(s)).Distinct().OrderBy(s => s))
            CategoryFilterBox.Items.Add(new ComboBoxItem { Content = c, Tag = c });

        if (!string.IsNullOrEmpty(selected))
        {
            foreach (ComboBoxItem item in CategoryFilterBox.Items)
            {
                if (item.Content?.ToString() == selected)
                {
                    item.IsSelected = true;
                    break;
                }
            }
        }
    }

    private void ApplyFilters()
    {
        var q = (SearchBox.Text ?? "").Trim().ToLowerInvariant();
        var catTag = (CategoryFilterBox.SelectedItem as ComboBoxItem)?.Tag?.ToString() ?? "";
        IEnumerable<Produit> list = _source ?? Enumerable.Empty<Produit>();
        if (!string.IsNullOrWhiteSpace(q))
            list = list.Where(p =>
                ((p?.Nom ?? "").ToLowerInvariant().Contains(q)) ||
                ((p?.BusinessName ?? "").ToLowerInvariant().Contains(q)) ||
                ((p?.Categorie ?? "").ToLowerInvariant().Contains(q)));
        if (!string.IsNullOrWhiteSpace(catTag))
            list = list.Where(p => string.Equals(p?.Categorie, catTag, StringComparison.OrdinalIgnoreCase));

        var sortTag = (SortBox.SelectedItem as ComboBoxItem)?.Tag?.ToString() ?? "exp";
        list = sortTag switch
        {
            "prix_asc" => list.OrderBy(p => p?.Prix).ThenBy(p => p?.Nom ?? ""),
            "prix_desc" => list.OrderByDescending(p => p?.Prix).ThenBy(p => p?.Nom ?? ""),
            "nom" => list.OrderBy(p => p?.Nom ?? ""),
            _ => list.OrderBy(p => p?.DateExpiration).ThenBy(p => p?.Nom ?? "")
        };

        CatalogItems.ItemsSource = list.ToList();
    }

    private async void RefreshBtn_Click(object sender, RoutedEventArgs e) => await LoadAsync();

    private void SearchBox_TextChanged(object sender, TextChangedEventArgs e)
    {
        if (!IsLoaded) return;
        ApplyFilters();
    }

    private void CategoryFilterBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
    {
        if (!IsLoaded) return;
        ApplyFilters();
    }

    private void SortBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
    {
        if (!IsLoaded) return;
        ApplyFilters();
    }

    private async void AddToCart_Click(object sender, RoutedEventArgs e)
    {
        if (Session.CurrentUser is null || !Session.IsClient)
        {
            ToastHelper.Error("Session", "Connexion client requise.");
            return;
        }

        if (sender is not Button btn || !int.TryParse(btn.Tag?.ToString(), out var productId))
            return;

        var dock = VisualTreeHelper.GetParent(btn) as DockPanel;
        var qtyBox = dock?.Children.OfType<TextBox>().FirstOrDefault();
        var qtyText = qtyBox?.Text?.Trim() ?? "1";
        if (!int.TryParse(qtyText, out var qty) || qty <= 0)
        {
            ToastHelper.Error("Quantité", "Indiquez une quantité valide (≥ 1).");
            return;
        }

        try
        {
            await _cart.AddOrUpdateQuantityAsync(Session.CurrentUser.Id, productId, qty);
            ToastHelper.Success("Panier", $"{qty} article(s) ajouté(s) au panier.");
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Panier", ex.Message);
        }
    }

    private async void ViewProduct_Click(object sender, RoutedEventArgs e)
    {
        if (sender is not Button b || !int.TryParse(b.Tag?.ToString(), out var productId))
            return;
        var dlg = new ProductDetailWindow(productId) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
        if (dlg.AddedToCart)
            ToastHelper.Success("Panier", "Article ajouté au panier.");
        await LoadAsync();
    }

    private async void CatalogCard_MouseLeftButtonUp(object sender, System.Windows.Input.MouseButtonEventArgs e)
    {
        if (sender is not Border border || border.DataContext is not Produit p)
            return;
        var dlg = new ProductDetailWindow(p.Id) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
        if (dlg.AddedToCart)
            ToastHelper.Success("Panier", "Article ajouté au panier.");
        await LoadAsync();
    }
}
