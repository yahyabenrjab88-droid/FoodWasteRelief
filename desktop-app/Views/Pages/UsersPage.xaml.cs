using System;
using System.Collections.Generic;
using System.Linq;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;
using FoodWasteRelief.Dialogs;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using FoodWasteRelief.Views.Details;

namespace FoodWasteRelief.Views.Pages;

public partial class UsersPage : Page
{
    private readonly UserService _service = new();
    private List<User> _all = new();

    public UsersPage()
    {
        InitializeComponent();
        Loaded += async (_, _) => await LoadAsync();
    }

    private async System.Threading.Tasks.Task LoadAsync()
    {
        UsersErrorBanner.Visibility = Visibility.Collapsed;
        UsersErrorText.Text = "";
        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            _all = await _service.GetAllAsync();
            ApplyFilter();
        }
        catch (Exception ex)
        {
            _all = new();
            ApplyFilter();
            UsersErrorText.Text = ex.Message;
            UsersErrorBanner.Visibility = Visibility.Visible;
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private void SearchBox_TextChanged(object sender, TextChangedEventArgs e) => ApplyFilter();
    private void RoleFilterBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
    {
        if (!IsLoaded) return;
        ApplyFilter();
    }
    private async void RefreshBtn_Click(object sender, RoutedEventArgs e) => await LoadAsync();

    private void ApplyFilter()
    {
        var q = (SearchBox.Text ?? "").Trim().ToLowerInvariant();
        var selectedRole = (RoleFilterBox.SelectedItem as ComboBoxItem)?.Content?.ToString() ?? "Tous les roles";

        IEnumerable<User> filtered = _all ?? Enumerable.Empty<User>();
        if (!string.IsNullOrWhiteSpace(q))
        {
            filtered = filtered.Where(u =>
                ((u?.FullName ?? "").ToLowerInvariant().Contains(q)) ||
                ((u?.Email ?? "").ToLowerInvariant().Contains(q)) ||
                ((u?.Role ?? "").ToLowerInvariant().Contains(q)));
        }

        if (!selectedRole.Equals("Tous les roles", StringComparison.OrdinalIgnoreCase))
            filtered = filtered.Where(u => string.Equals(u?.Role, selectedRole, StringComparison.OrdinalIgnoreCase));

        GridUsers.ItemsSource = filtered.ToList();
    }

    private async void DeleteUser_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;

        var owner = Window.GetWindow(this);
        if (!ConfirmDialog.Show(owner, "Confirmation", "Supprimer cet utilisateur ?", destructive: true, "Supprimer"))
            return;

        Mouse.OverrideCursor = Cursors.Wait;
        try
        {
            await _service.DeleteAsync(id);
            await ActivityLogger.Log(Session.CurrentUser?.Id ?? 0, Session.CurrentUser?.Role ?? "admin",
                Session.CurrentUser?.FullName ?? "admin", "User Deleted", $"User id={id} deleted.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            UsersErrorText.Text = ex.Message;
            UsersErrorBanner.Visibility = Visibility.Visible;
        }
        finally
        {
            Mouse.OverrideCursor = null;
        }
    }

    private void ViewUser_Click(object sender, RoutedEventArgs e)
    {
        if (!TryGetIdFromSender(sender, out var id)) return;
        var dlg = new UserDetailWindow(id) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
    }

    private static bool TryGetIdFromSender(object sender, out int id)
    {
        id = 0;
        var tag = (sender as FrameworkElement)?.Tag?.ToString();
        return int.TryParse(tag, out id);
    }
}

