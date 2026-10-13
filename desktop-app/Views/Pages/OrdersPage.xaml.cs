using System;
using System.Collections.Generic;
using System.Linq;
using System.Windows;
using System.Windows.Controls;
using FoodWasteRelief.Dialogs;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using FoodWasteRelief.Views.Details;

namespace FoodWasteRelief.Views.Pages;

public partial class OrdersPage : Page
{
    private readonly OrderService _service = new();
    private List<Commande> _all = new();

    public OrdersPage()
    {
        InitializeComponent();
        Loaded += OrdersPage_Loaded;
    }

    private async void OrdersPage_Loaded(object sender, RoutedEventArgs e)
    {
        AdminOrdersActionsColumn.Visibility = Session.IsAdmin ? Visibility.Visible : Visibility.Collapsed;
        BusinessOrdersActionsColumn.Visibility = Session.IsClient ? Visibility.Collapsed
            : (Session.IsBusiness || Session.IsAdmin) ? Visibility.Visible : Visibility.Collapsed;
        await LoadAsync();
    }

    private async System.Threading.Tasks.Task LoadAsync()
    {
        try
        {
            if (Session.IsClient && Session.CurrentUser is not null)
                _all = await _service.GetByClientAsync(Session.CurrentUser.Id);
            else if (Session.IsBusiness && Session.LinkedBusinessId.HasValue)
                _all = await _service.GetByBusinessAsync(Session.LinkedBusinessId.Value);
            else if (Session.IsBusiness)
            {
                _all = new List<Commande>();
                ToastHelper.Error("Commandes", "Aucun etablissement lie a ce compte.");
            }
            else
                _all = await _service.GetAllAsync();

            foreach (var o in _all)
                OrderStatusHelper.ApplyCommandeUi(o, Session.IsClient, Session.IsAdmin, Session.IsBusiness, Session.LinkedBusinessId);

            ApplyFilters();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Commandes", ex.Message);
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
        var statusTag = (StatusFilterBox.SelectedItem as ComboBoxItem)?.Tag?.ToString()?.Trim() ?? "";
        IEnumerable<Commande> result = _all ?? Enumerable.Empty<Commande>();
        if (!string.IsNullOrWhiteSpace(q))
        {
            if (Session.IsClient)
                result = result.Where(o => ((o?.BusinessName ?? "").ToLowerInvariant().Contains(q)) || ((o?.Status ?? "").ToLowerInvariant().Contains(q)) || ((o?.StatusLabelFr ?? "").ToLowerInvariant().Contains(q)));
            else
                result = result.Where(o => ((o?.ClientName ?? "").ToLowerInvariant().Contains(q)) || ((o?.BusinessName ?? "").ToLowerInvariant().Contains(q)) || ((o?.Status ?? "").ToLowerInvariant().Contains(q)) || ((o?.StatusLabelFr ?? "").ToLowerInvariant().Contains(q)));
        }

        if (!string.IsNullOrEmpty(statusTag))
            result = result.Where(o => string.Equals(OrderStatusHelper.Normalize(o?.Status), statusTag, StringComparison.OrdinalIgnoreCase));

        GridOrders.ItemsSource = result.ToList();
    }

    private async void ConfirmOrder_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        if (!ConfirmDialog.Show(Window.GetWindow(this), "Confirmer la commande", "Passer cette commande en statut « Confirmée » ?", destructive: false, "Confirmer"))
            return;
        await TryUpdateStatusAsync(id, OrderStatusHelper.Confirmee, "Commande confirmée");
    }

    private async void MarkDelivered_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        if (!ConfirmDialog.Show(Window.GetWindow(this), "Livraison", "Marquer cette commande comme « Livrée » ?", destructive: false, "Marquer livrée"))
            return;
        await TryUpdateStatusAsync(id, OrderStatusHelper.Livree, "Commande livrée");
    }

    private async void CancelOrder_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        if (!ConfirmDialog.Show(Window.GetWindow(this), "Annuler la commande", "Annuler cette commande et réintégrer les quantités au stock ?", destructive: true, "Annuler"))
            return;
        await TryUpdateStatusAsync(id, OrderStatusHelper.Annulee, "Commande annulée");
    }

    private async System.Threading.Tasks.Task TryUpdateStatusAsync(int id, string targetStatus, string toastTitle)
    {
        try
        {
            await _service.UpdateStatusAsync(id, targetStatus, Session.LinkedBusinessId, Session.IsAdmin);
            await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "",
                Session.CurrentUser?.FullName ?? "", "Mise à jour statut commande", $"Commande #{id} → {OrderStatusHelper.ToFrenchLabel(targetStatus)}.");
            ToastHelper.Success(toastTitle, "Le statut a été enregistré.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Commandes", ex.Message);
        }
    }

    private async void Delete_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        if (!ConfirmDialog.Show(Window.GetWindow(this), "Supprimer la commande", "Supprimer définitivement cette commande et ses lignes ?", destructive: true, "Supprimer"))
            return;
        try
        {
            await _service.DeleteAsync(id);
            await ActivityLogHelper.LogActivityAsync(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "",
                Session.CurrentUser?.FullName ?? "", "Suppression commande", $"Commande #{id} supprimée.");
            ToastHelper.Info("Commandes", "Commande supprimée.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Commandes", ex.Message);
        }
    }

    private static bool TryGetIdFromSender(object sender, out int id)
    {
        id = 0;
        var tag = (sender as FrameworkElement)?.Tag;
        if (tag is int i)
        {
            id = i;
            return true;
        }

        return int.TryParse(tag?.ToString(), out id);
    }

    private async void ViewOrder_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        var dlg = new OrderDetailWindow(id) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
        if (dlg.Changed)
            await LoadAsync();
    }
}
