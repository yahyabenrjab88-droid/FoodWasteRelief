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

public partial class DonationsPage : Page
{
    private readonly DonationService _donations = new();
    private readonly StorageService _storage = new();
    private readonly AssociationService _associations = new();
    private string _associationFilter = "All";

    public DonationsPage()
    {
        InitializeComponent();
        Loaded += async (_, _) =>
        {
            AssociationFilterPanel.Visibility = Session.IsAssociation ? Visibility.Visible : Visibility.Collapsed;
            await LoadAsync();
        };
    }

    private void ClearError()
    {
        ErrorBanner.Visibility = Visibility.Collapsed;
        ErrorBannerText.Text = "";
    }

    private void ShowError(string message)
    {
        ErrorBannerText.Text = message;
        ErrorBanner.Visibility = Visibility.Visible;
    }

    private void ApplyAssociationColumnVisibility()
    {
        if (GridDonations.Columns.Count > 2)
            GridDonations.Columns[2].Visibility = Session.IsAssociation ? Visibility.Collapsed : Visibility.Visible;
    }

    private static void ApplyRowFlags(IReadOnlyList<Donation> list)
    {
        foreach (var d in list)
        {
            var pending = string.Equals(d.Status, "Pending", StringComparison.OrdinalIgnoreCase);
            d.ShowAdminAcceptReject = Session.IsAdmin && pending;
            d.ShowAssociationCollect = Session.IsAssociation &&
                                       string.Equals(d.Status, "Accepted", StringComparison.OrdinalIgnoreCase);
            d.ShowDeleteDonation = Session.IsAdmin || Session.IsBusiness;
        }
    }

    private async Task LoadAsync()
    {
        ClearError();
        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            CreateDonationPanel.Visibility = Session.IsBusiness ? Visibility.Visible : Visibility.Collapsed;

            List<Donation> list;
            if (Session.IsAssociation && Session.LinkedAssociationId.HasValue)
            {
                list = await _donations.GetDonationsForAssociationAsync(Session.LinkedAssociationId.Value, _associationFilter);
            }
            else if (Session.IsAssociation)
            {
                list = new List<Donation>();
                ShowError("Aucune association liee a ce compte.");
            }
            else if (Session.IsBusiness && Session.LinkedBusinessId.HasValue)
                list = await _donations.GetByBusinessAsync(Session.LinkedBusinessId.Value);
            else if (Session.IsBusiness)
            {
                list = new List<Donation>();
                ShowError("Aucun etablissement lie a ce compte.");
            }
            else
                list = await _donations.GetAllAsync();

            ApplyRowFlags(list);
            GridDonations.ItemsSource = list.ToList();
            ApplyAssociationColumnVisibility();

            if (Session.IsBusiness)
                await LoadSelectorsAsync();
        }
        catch (Exception ex)
        {
            GridDonations.ItemsSource = new List<Donation>();
            ShowError(ex.Message);
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private async Task LoadSelectorsAsync()
    {
        try
        {
            if (Session.IsBusiness && Session.LinkedBusinessId.HasValue)
            {
                var items = await _storage.GetByBusinessAsync(Session.LinkedBusinessId.Value);
                StorageBox.ItemsSource = items.FindAll(s => s.Quantity > 0);
            }
            else
                StorageBox.ItemsSource = new List<StorageItem>();

            AssociationBox.ItemsSource = await _associations.GetAllAsync();
        }
        catch (Exception ex)
        {
            ShowError(ex.Message);
        }
    }

    private async void AssociationStatusFilter_SelectionChanged(object sender, SelectionChangedEventArgs e)
    {
        if (!IsLoaded || !Session.IsAssociation)
            return;
        if (AssociationStatusFilter.SelectedItem is not ComboBoxItem item || item.Tag is not string tag)
            return;
        _associationFilter = tag;
        await LoadAsync();
    }

    private async void RefreshBtn_Click(object sender, RoutedEventArgs e) => await LoadAsync();

    private async void CreateDonation_Click(object sender, RoutedEventArgs e)
    {
        ClearError();
        if (Session.IsAdmin)
        {
            ShowError("L'administrateur ne peut pas créer un don depuis cette page.");
            return;
        }

        if (StorageBox.SelectedItem is not StorageItem stockRow)
        {
            ShowError("Choisissez une ligne de stock.");
            return;
        }

        if (AssociationBox.SelectedItem is not Association association)
        {
            ShowError("Choisissez une association.");
            return;
        }

        if (!int.TryParse(QtyBox.Text, out var qty) || qty <= 0)
        {
            ShowError("Quantité invalide.");
            return;
        }

        if (stockRow.Quantity < qty)
        {
            ShowError("Quantité supérieure au stock disponible.");
            return;
        }

        if (!Session.LinkedBusinessId.HasValue)
        {
            ShowError("Établissement non lié à la session.");
            return;
        }

        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            var id = await _donations.CreateFromStorageAsync(stockRow.Id, association.Id, qty, Session.LinkedBusinessId.Value);
            await ActivityLogger.Log(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "",
                Session.CurrentUser?.FullName ?? "", "Don créé", $"Don #{id}, stock « {stockRow.ItemName} », qté {qty}.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ShowError(ex.Message);
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private async void Delete_Click(object sender, RoutedEventArgs e)
    {
        ClearError();
        if (!TryGetIdFromSender(sender, out var id))
            return;

        var owner = Window.GetWindow(this);
        if (!ConfirmDialog.Show(owner, "Confirmation", "Supprimer ce don ? Les quantités seront réintégrées au stock (ou au produit pour les anciens dons).", destructive: true, "Supprimer"))
            return;

        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            await _donations.DeleteAsync(id);
            await ActivityLogger.Log(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "",
                Session.CurrentUser?.FullName ?? "", "Donation Deleted", $"Donation id={id} deleted.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ShowError(ex.Message);
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private async void AcceptDonation_Click(object sender, RoutedEventArgs e)
    {
        ClearError();
        if (!Session.IsAdmin)
            return;
        if (!TryGetIdFromSender(sender, out var id))
            return;

        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            await _donations.UpdateStatusAsync(id, "Accepted");
            await ActivityLogger.Log(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "admin",
                Session.CurrentUser?.FullName ?? "admin", "Donation Accepted", $"Donation id={id} accepted.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ShowError(ex.Message);
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private async void RejectDonation_Click(object sender, RoutedEventArgs e)
    {
        ClearError();
        if (!Session.IsAdmin)
            return;
        if (!TryGetIdFromSender(sender, out var id))
            return;

        var owner = Window.GetWindow(this);
        if (!ConfirmDialog.Show(owner, "Confirmation", "Rejeter ce don ?", destructive: true, "Rejeter"))
            return;

        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            await _donations.UpdateStatusAsync(id, "Rejected");
            await ActivityLogger.Log(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "admin",
                Session.CurrentUser?.FullName ?? "admin", "Donation Rejected", $"Donation id={id} rejected.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ShowError(ex.Message);
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private async void MarkCollected_Click(object sender, RoutedEventArgs e)
    {
        ClearError();
        if (!Session.IsAssociation || !Session.LinkedAssociationId.HasValue)
            return;
        if (!TryGetIdFromSender(sender, out var id))
            return;

        var owner = Window.GetWindow(this);
        if (!ConfirmDialog.Show(owner, "Confirmation", "Marquer ce don comme collecté ?", destructive: false, "Marquer collecté"))
            return;

        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            var ok = await _donations.MarkCollectedByAssociationAsync(id, Session.LinkedAssociationId.Value);
            if (!ok)
            {
                ShowError("Impossible de marquer ce don comme collecté (statut ou association incorrects).");
                return;
            }

            await ActivityLogger.Log(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "association",
                Session.CurrentUser?.FullName ?? "", "Donation Collected", $"Donation id={id} marked collected.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
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

    private async void ViewDonation_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        var dlg = new DonationDetailWindow(id) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
        if (dlg.Changed)
            await LoadAsync();
    }
}
