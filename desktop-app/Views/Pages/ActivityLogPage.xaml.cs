using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using FoodWasteRelief.Views.Details;

namespace FoodWasteRelief.Views.Pages;

public partial class ActivityLogPage : Page
{
    private readonly ActivityLogService _service = new();
    private List<ActivityLogEntry> _all = new();

    public ActivityLogPage()
    {
        InitializeComponent();
        Loaded += async (_, _) => await LoadAsync();
    }

    private async Task LoadAsync()
    {
        try
        {
            SubtitleText.Text = Session.IsAdmin
                ? "Toutes les actions enregistrées sur la plateforme (les plus récentes en premier)."
                : "Actions effectuées avec votre compte (les plus récentes en premier).";

            var uid = Session.CurrentUser?.Id ?? 0;
            _all = await _service.GetRecentAsync(Session.IsAdmin, uid);
            ApplyFilter();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Activité", ex.Message);
            _all = new List<ActivityLogEntry>();
            GridLogs.ItemsSource = _all;
        }
    }

    private void ApplyFilter()
    {
        var q = (SearchBox.Text ?? "").Trim().ToLowerInvariant();
        IEnumerable<ActivityLogEntry> rows = _all;
        if (!string.IsNullOrEmpty(q))
        {
            rows = rows.Where(e =>
                (e.Username ?? "").ToLowerInvariant().Contains(q) ||
                (e.UserRole ?? "").ToLowerInvariant().Contains(q) ||
                (e.Action ?? "").ToLowerInvariant().Contains(q) ||
                (e.Details ?? "").ToLowerInvariant().Contains(q));
        }

        GridLogs.ItemsSource = rows.ToList();
    }

    private async void RefreshBtn_Click(object sender, RoutedEventArgs e) => await LoadAsync();

    private void SearchBox_TextChanged(object sender, TextChangedEventArgs e)
    {
        if (!IsLoaded) return;
        ApplyFilter();
    }

    private void ViewLog_Click(object sender, RoutedEventArgs e)
    {
        var tag = (sender as FrameworkElement)?.Tag?.ToString();
        if (!int.TryParse(tag, out var id)) return;
        var dlg = new ActivityLogDetailWindow(id) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
    }
}
