using System;
using System.Collections.Generic;
using System.Globalization;
using System.IO;
using System.Linq;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Media.Imaging;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using Microsoft.Win32;

namespace FoodWasteRelief.Dialogs;

public partial class AddEditProductDialog : Window
{
    private sealed class StockRow
    {
        public int Id { get; init; }
        public decimal Quantity { get; init; }
        public string Unit { get; init; } = "";
        public string Label { get; init; } = "";
    }

    private readonly int _businessId;
    private readonly StorageService _storage = new();
    private readonly ProductService _products = new();
    private string? _imagePath;

    public AddEditProductDialog(int businessId)
    {
        _businessId = businessId;
        InitializeComponent();
        Loaded += async (_, _) => await OnLoadedAsync();
    }

    private async Task OnLoadedAsync()
    {
        foreach (var c in new[] { "Boulangerie", "Plats cuisinés", "Fruits & légumes", "Boissons", "Épicerie", "Autre" })
            CategorieBox.Items.Add(new ComboBoxItem { Content = c });
        if (CategorieBox.Items.Count > 0)
            CategorieBox.SelectedIndex = 0;

        try
        {
            var items = await _storage.GetByBusinessAsync(_businessId);
            var rows = items.Select(s => new StockRow
            {
                Id = s.Id,
                Quantity = s.Quantity,
                Unit = s.Unit,
                Label = $"{s.ItemName} ({s.Quantity.ToString(CultureInfo.InvariantCulture)} {s.Unit})"
            }).ToList();
            StockCombo.ItemsSource = rows;
            if (rows.Count > 0)
                StockCombo.SelectedIndex = 0;
            UpdateRemainingHint();
        }
        catch (Exception ex)
        {
            SaveErrorText.Text = ex.Message;
            SaveErrorText.Visibility = Visibility.Visible;
        }
    }

    private void StockCombo_SelectionChanged(object sender, SelectionChangedEventArgs e) => UpdateRemainingHint();

    private void QtyListedBox_TextChanged(object sender, TextChangedEventArgs e) => UpdateRemainingHint();

    private void UpdateRemainingHint()
    {
        if (StockCombo.SelectedItem is not StockRow row)
        {
            RemainingHint.Text = "";
            return;
        }

        if (!int.TryParse((QtyListedBox.Text ?? "").Trim(), out var q) || q < 0)
        {
            RemainingHint.Text = $"Stock restant sur cette ligne : {row.Quantity.ToString(CultureInfo.InvariantCulture)} {row.Unit}";
            return;
        }

        var left = row.Quantity - q;
        RemainingHint.Text = $"Après création : {left.ToString(CultureInfo.InvariantCulture)} {row.Unit} restant(s) en stock.";
    }

    private void PickImage_Click(object sender, RoutedEventArgs e)
    {
        var dlg = new OpenFileDialog
        {
            Filter = "Images|*.png;*.jpg;*.jpeg;*.webp|All files|*.*"
        };
        if (dlg.ShowDialog() != true)
            return;

        _imagePath = dlg.FileName;
        ImagePathText.Text = Path.GetFileName(_imagePath);
        try
        {
            var bmp = new BitmapImage();
            bmp.BeginInit();
            bmp.UriSource = new Uri(_imagePath, UriKind.Absolute);
            bmp.CacheOption = BitmapCacheOption.OnLoad;
            bmp.EndInit();
            PreviewImage.Source = bmp;
        }
        catch
        {
            PreviewImage.Source = null;
        }
    }

    private void Cancel_Click(object sender, RoutedEventArgs e)
    {
        DialogResult = false;
        Close();
    }

    private async void Save_Click(object sender, RoutedEventArgs e)
    {
        SaveErrorText.Visibility = Visibility.Collapsed;
        SaveErrorText.Text = "";

        var nom = (NomBox.Text ?? "").Trim();
        var desc = (DescBox.Text ?? "").Trim();
        var cat = (CategorieBox.SelectedItem as ComboBoxItem)?.Content?.ToString()?.Trim() ?? "";
        if (!decimal.TryParse((PrixBox.Text ?? "").Trim().Replace(',', '.'), NumberStyles.Any, CultureInfo.InvariantCulture, out var prix))
        {
            SaveErrorText.Text = "Prix invalide.";
            SaveErrorText.Visibility = Visibility.Visible;
            return;
        }

        if (StockCombo.SelectedItem is not StockRow stock)
        {
            SaveErrorText.Text = "Choisissez une ligne de stock.";
            SaveErrorText.Visibility = Visibility.Visible;
            return;
        }

        if (!int.TryParse((QtyListedBox.Text ?? "").Trim(), out var qty) || qty <= 0)
        {
            SaveErrorText.Text = "Indiquez une quantité entière supérieure à 0.";
            SaveErrorText.Visibility = Visibility.Visible;
            return;
        }

        if (ExpiryPicker.SelectedDate is null)
        {
            SaveErrorText.Text = "Choisissez une date d'expiration.";
            SaveErrorText.Visibility = Visibility.Visible;
            return;
        }

        var type = (TypeBox.SelectedItem as ComboBoxItem)?.Content?.ToString()?.Trim() ?? "vente";
        if (string.IsNullOrWhiteSpace(nom) || string.IsNullOrWhiteSpace(cat))
        {
            SaveErrorText.Text = "Remplissez les champs obligatoires.";
            SaveErrorText.Visibility = Visibility.Visible;
            return;
        }

        if (qty > stock.Quantity)
        {
            SaveErrorText.Text = "Quantité supérieure au stock disponible.";
            SaveErrorText.Visibility = Visibility.Visible;
            return;
        }

        string? imageUrl = null;
        try
        {
            if (!string.IsNullOrWhiteSpace(_imagePath))
                imageUrl = await CloudinaryHelper.UploadImageAsync(_imagePath);

            var product = new Produit
            {
                BusinessId = _businessId,
                Nom = nom,
                Description = desc,
                Categorie = cat,
                Prix = prix,
                DateExpiration = ExpiryPicker.SelectedDate!.Value.Date.AddHours(12),
                Type = type
            };

            var newId = await _products.CreateFromStockAsync(product, stock.Id, qty, imageUrl);
            await ActivityLogger.Log(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "business",
                Session.CurrentUser?.FullName ?? "", "Product Created", $"product id={newId}, stock={stock.Id}, qty={qty}.");

            DialogResult = true;
            Close();
        }
        catch (Exception ex)
        {
            SaveErrorText.Text = ex.Message;
            SaveErrorText.Visibility = Visibility.Visible;
        }
    }
}
