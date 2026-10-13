using System;
using System.Collections.Generic;
using System.Linq;
using System.Windows;
using System.Windows.Controls;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using FoodWasteRelief.Views.Details;

namespace FoodWasteRelief.Views.Pages;

public partial class BusinessesPage : Page
{
    private readonly BusinessService _service = new();
    private List<Business> _all = new();

    public BusinessesPage()
    {
        InitializeComponent();
        Loaded += async (_, _) => await LoadAsync();
    }

    private async System.Threading.Tasks.Task LoadAsync()
    {
        try
        {
            _all = await _service.GetAllWithOwnerAsync();
            ApplyFilters();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private async void RefreshBtn_Click(object sender, RoutedEventArgs e) => await LoadAsync();
    private void SearchBox_TextChanged(object sender, TextChangedEventArgs e) => ApplyFilters();
    private void StatusFilterBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
    {
        if (!IsLoaded) return;
        ApplyFilters();
    }

    private void ApplyFilters()
    {
        var q = (SearchBox.Text ?? "").Trim().ToLowerInvariant();
        var status = (StatusFilterBox.SelectedItem as ComboBoxItem)?.Content?.ToString() ?? "Tous";
        IEnumerable<Business> result = _all ?? Enumerable.Empty<Business>();
        if (!string.IsNullOrWhiteSpace(q))
            result = result.Where(b =>
                (b?.NomBusiness?.ToLowerInvariant().Contains(q) ?? false) ||
                (b?.Ville?.ToLowerInvariant().Contains(q) ?? false));
        if (!status.Equals("Tous", StringComparison.OrdinalIgnoreCase))
            result = result.Where(b => string.Equals(b?.Status, status, StringComparison.OrdinalIgnoreCase));
        GridBusinesses.ItemsSource = result.ToList();
    }

    private async void Approve_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        try
        {
            await _service.ApproveAsync(id);
            await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "admin",
                Session.CurrentUser?.FullName ?? "admin", "Business Approved", $"Business id={id} approved.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private async void Reject_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        try
        {
            await _service.RejectAsync(id);
            await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "admin",
                Session.CurrentUser?.FullName ?? "admin", "Business Rejected", $"Business id={id} rejected.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private async void Delete_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        if (MessageBox.Show("Supprimer cet etablissement ?", "Confirmation", MessageBoxButton.YesNo, MessageBoxImage.Question) != MessageBoxResult.Yes)
            return;
        try
        {
            await _service.DeleteAsync(id);
            await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "admin",
                Session.CurrentUser?.FullName ?? "admin", "Business Deleted", $"Business id={id} deleted.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private void View_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        var dlg = new EstablishmentDetailWindow(id) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
    }

    private static bool TryGetIdFromSender(object sender, out int id)
    {
        id = 0;
        var tag = (sender as FrameworkElement)?.Tag?.ToString();
        return int.TryParse(tag, out id);
    }
}

