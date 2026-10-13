using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;
using FoodWasteRelief.Dialogs;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using FoodWasteRelief.Views.Details;

namespace FoodWasteRelief.Views.Pages;

public partial class ProductsPage : Page
{
    private readonly ProductService _service = new();
    private List<Produit> _all = new();

    public ProductsPage()
    {
        InitializeComponent();
        Loaded += async (_, _) =>
        {
            BtnAddProduct.Visibility = Session.IsBusiness && Session.LinkedBusinessId.HasValue
                ? Visibility.Visible
                : Visibility.Collapsed;
            await LoadAsync();
        };
    }

    private void ClearError()
    {
        ProductsErrorBanner.Visibility = Visibility.Collapsed;
        ProductsErrorText.Text = "";
    }

    private void ShowError(string message)
    {
        ProductsErrorText.Text = message;
        ProductsErrorBanner.Visibility = Visibility.Visible;
    }

    private async Task LoadAsync()
    {
        ClearError();
        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            if (Session.IsBusiness && Session.LinkedBusinessId.HasValue)
                _all = await _service.GetByBusinessAsync(Session.LinkedBusinessId.Value);
            else if (Session.IsBusiness)
            {
                _all = new();
                ShowError("Aucun etablissement lie a ce compte.");
            }
            else
                _all = await _service.GetAllAsync();
            ApplyFilters();
        }
        catch (Exception ex)
        {
            _all = new();
            ApplyFilters();
            ShowError(ex.Message);
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private async void RefreshBtn_Click(object sender, RoutedEventArgs e) => await LoadAsync();

    private async void AddProduct_Click(object sender, RoutedEventArgs e)
    {
        if (!Session.IsBusiness || !Session.LinkedBusinessId.HasValue)
            return;

        var dlg = new AddEditProductDialog(Session.LinkedBusinessId.Value)
        {
            Owner = Window.GetWindow(this)
        };
        if (dlg.ShowDialog() != true)
            return;

        ToastHelper.Success("Produit", "Produit créé et stock mis à jour.");
        await LoadAsync();
    }

    private void SearchBox_TextChanged(object sender, TextChangedEventArgs e) => ApplyFilters();

    private void TypeFilterBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
    {
        if (!IsLoaded) return;
        ApplyFilters();
    }

    private void ApplyFilters()
    {
        var q = (SearchBox.Text ?? "").Trim().ToLowerInvariant();
        var type = (TypeFilterBox.SelectedItem as ComboBoxItem)?.Content?.ToString() ?? "Tous";
        IEnumerable<Produit> result = _all ?? Enumerable.Empty<Produit>();
        if (!string.IsNullOrWhiteSpace(q))
            result = result.Where(p =>
                ((p?.Nom ?? "").ToLowerInvariant().Contains(q)) ||
                ((p?.BusinessName ?? "").ToLowerInvariant().Contains(q)) ||
                ((p?.Categorie ?? "").ToLowerInvariant().Contains(q)));
        if (!type.Equals("Tous", StringComparison.OrdinalIgnoreCase))
            result = result.Where(p => string.Equals(p?.Type, type, StringComparison.OrdinalIgnoreCase));

        var list = result.ToList();
        foreach (var p in list)
            p.ShowRetireProduct = p.IsActive && (Session.IsAdmin || Session.IsBusiness);

        GridProducts.ItemsSource = list;
    }

    private async void Delete_Click(object sender, RoutedEventArgs e)
    {
        ClearError();
        if (!TryGetIdFromSender(sender, out var id))
            return;

        var owner = Window.GetWindow(this);
        if (!ConfirmDialog.Show(owner, "Retirer le produit", "Retirer ce produit du catalogue ? Le stock d'origine sera recrédité.", destructive: true, "Retirer"))
            return;

        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            await _service.DeleteAsync(id);
            await ActivityLogger.Log(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "",
                Session.CurrentUser?.FullName ?? "", "Product Deleted", $"product id={id} soft-deleted / stock restored.");
            ToastHelper.Success("Produit", "Produit retiré du catalogue.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Erreur", ex.Message);
            ShowError(ex.Message);
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private static bool TryGetIdFromSender(object sender, out int id)
    {
        id = 0;
        var tag = (sender as FrameworkElement)?.Tag?.ToString();
        return int.TryParse(tag, out id);
    }

    private void ViewProduct_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        var dlg = new ProductDetailWindow(id) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
    }
}
