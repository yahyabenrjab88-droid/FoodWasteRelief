using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using FoodWasteRelief.Views.Details;

namespace FoodWasteRelief.Views.Pages;

public partial class StoragePage : Page
{
    private readonly StorageService _service = new();
    private List<StorageItem> _items = new();
    private int? _editingId;

    public StoragePage()
    {
        InitializeComponent();
        Loaded += async (_, _) => await LoadAsync();
    }

    private async Task LoadAsync()
    {
        try
        {
            if (!Session.LinkedBusinessId.HasValue)
            {
                GridStorage.ItemsSource = null;
                return;
            }

            _items = await _service.GetByBusinessAsync(Session.LinkedBusinessId.Value);
            GridStorage.ItemsSource = _items;
            ResetForm();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private void ResetForm()
    {
        _editingId = null;
        ItemNameBox.Text = "";
        QtyBox.Text = "";
        UnitBox.SelectedIndex = -1;
        ExpiryBox.SelectedDate = null;
        AddOrUpdateBtn.Content = "Ajouter";
    }

    private async void RefreshBtn_Click(object sender, RoutedEventArgs e) => await LoadAsync();

    private async void AddOrUpdate_Click(object sender, RoutedEventArgs e)
    {
        if (!Session.LinkedBusinessId.HasValue) return;
        if (string.IsNullOrWhiteSpace(ItemNameBox.Text) || !decimal.TryParse(QtyBox.Text, out var qty) || qty <= 0 || UnitBox.SelectedItem is not ComboBoxItem unitItem)
        {
            MessageBox.Show("Verifier les champs obligatoires.", "Validation", MessageBoxButton.OK, MessageBoxImage.Information);
            return;
        }

        var model = new StorageItem
        {
            Id = _editingId ?? 0,
            BusinessId = Session.LinkedBusinessId.Value,
            ItemName = ItemNameBox.Text.Trim(),
            Quantity = qty,
            Unit = unitItem.Content?.ToString() ?? "",
            ExpiryDate = ExpiryBox.SelectedDate
        };

        try
        {
            if (_editingId.HasValue)
            {
                await _service.UpdateAsync(model);
                await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "business",
                    Session.CurrentUser?.FullName ?? "", "Storage Updated", model.ItemName);
            }
            else
            {
                await _service.CreateAsync(model);
                await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "business",
                    Session.CurrentUser?.FullName ?? "", "Storage Created", model.ItemName);
            }

            await LoadAsync();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private void Edit_Click(object sender, RoutedEventArgs e)
    {
        if ((sender as FrameworkElement)?.Tag?.ToString() is not { } tag || !int.TryParse(tag, out var id))
            return;
        var item = _items.Find(x => x.Id == id);
        if (item is null) return;

        _editingId = item.Id;
        ItemNameBox.Text = item.ItemName;
        QtyBox.Text = item.Quantity.ToString("0.##");
        foreach (ComboBoxItem combo in UnitBox.Items)
        {
            if (string.Equals(combo.Content?.ToString(), item.Unit, StringComparison.OrdinalIgnoreCase))
            {
                UnitBox.SelectedItem = combo;
                break;
            }
        }
        ExpiryBox.SelectedDate = item.ExpiryDate;
        AddOrUpdateBtn.Content = "Modifier";
    }

    private async void Delete_Click(object sender, RoutedEventArgs e)
    {
        if ((sender as FrameworkElement)?.Tag?.ToString() is not { } tag || !int.TryParse(tag, out var id))
            return;
        if (MessageBox.Show("Supprimer cet item ?", "Confirm", MessageBoxButton.YesNo, MessageBoxImage.Question) != MessageBoxResult.Yes)
            return;
        try
        {
            await _service.DeleteAsync(id);
            await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "business",
                Session.CurrentUser?.FullName ?? "", "Storage Deleted", $"id={id}");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private void View_Click(object sender, RoutedEventArgs e)
    {
        if ((sender as FrameworkElement)?.Tag?.ToString() is not { } tag || !int.TryParse(tag, out var id))
            return;
        var dlg = new StockItemDetailWindow(id) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
    }
}
