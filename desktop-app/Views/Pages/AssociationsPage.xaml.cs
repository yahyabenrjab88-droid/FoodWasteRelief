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

public partial class AssociationsPage : Page
{
    private readonly AssociationService _service = new();
    private List<Association> _all = new();

    public AssociationsPage()
    {
        InitializeComponent();
        Loaded += async (_, _) => await LoadAsync();
    }

    private async System.Threading.Tasks.Task LoadAsync()
    {
        try
        {
            _all = await _service.GetAllAsync();
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
        IEnumerable<Association> result = _all ?? Enumerable.Empty<Association>();
        if (!string.IsNullOrWhiteSpace(q))
            result = result.Where(a =>
                ((a?.Nom ?? "").ToLowerInvariant().Contains(q)) ||
                ((a?.Ville ?? "").ToLowerInvariant().Contains(q)));
        if (!status.Equals("Tous", StringComparison.OrdinalIgnoreCase))
            result = result.Where(a => string.Equals(a?.Status, status, StringComparison.OrdinalIgnoreCase));
        GridAssociations.ItemsSource = result.ToList();
    }

    private async void Approve_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        try
        {
            await _service.ApproveAsync(id);
            await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "admin",
                Session.CurrentUser?.FullName ?? "admin", "Association Approved", $"Association id={id} approved.");
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
                Session.CurrentUser?.FullName ?? "admin", "Association Rejected", $"Association id={id} rejected.");
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
        if (MessageBox.Show("Supprimer cette association ?", "Confirmation", MessageBoxButton.YesNo, MessageBoxImage.Question) != MessageBoxResult.Yes)
            return;
        try
        {
            await _service.DeleteAsync(id);
            await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "admin",
                Session.CurrentUser?.FullName ?? "admin", "Association Deleted", $"Association id={id} deleted.");
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
        var dlg = new AssociationDetailWindow(id) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
    }

    private static bool TryGetIdFromSender(object sender, out int id)
    {
        id = 0;
        var tag = (sender as FrameworkElement)?.Tag?.ToString();
        return int.TryParse(tag, out id);
    }
}

