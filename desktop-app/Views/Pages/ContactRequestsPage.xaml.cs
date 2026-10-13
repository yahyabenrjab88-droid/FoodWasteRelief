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

public partial class ContactRequestsPage : Page
{
    private readonly ContactRequestService _service = new();
    private List<ContactRequest> _items = new();

    public ContactRequestsPage()
    {
        InitializeComponent();
        Loaded += async (_, _) => await LoadAsync();
    }

    private async Task LoadAsync()
    {
        try
        {
            ComposePanel.Visibility = Session.IsAdmin ? Visibility.Collapsed : Visibility.Visible;
            _items = Session.IsAdmin
                ? await _service.GetAllAsync()
                : await _service.GetBySenderAsync(Session.CurrentUser?.Id ?? 0);
            GridRequests.ItemsSource = _items;
            if (Session.IsAdmin)
            {
                var unread = await _service.GetUnreadCountAsync();
                UnreadText.Text = $"Non lus: {unread}";
            }
            else
            {
                UnreadText.Text = string.Empty;
            }
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private async void Send_Click(object sender, RoutedEventArgs e)
    {
        var subject = (SubjectBox.SelectedItem as ComboBoxItem)?.Content?.ToString() ?? "";
        var body = (BodyBox.Text ?? "").Trim();
        if (string.IsNullOrWhiteSpace(subject) || string.IsNullOrWhiteSpace(body))
        {
            MessageBox.Show("Subject et body sont obligatoires.", "Validation", MessageBoxButton.OK, MessageBoxImage.Information);
            return;
        }

        try
        {
            var req = new ContactRequest
            {
                SenderId = Session.CurrentUser?.Id ?? 0,
                SenderRole = Session.CurrentUser?.Role ?? "",
                SenderName = Session.CurrentUser?.FullName ?? "",
                Subject = subject,
                Body = body
            };
            await _service.CreateAsync(req);
            await ActivityLogHelper.LogActivityAsync(req.SenderId, req.SenderRole, req.SenderName, "Contact Request Sent", subject);
            BodyBox.Text = "";
            MessageBox.Show("Sent successfully.", "Info", MessageBoxButton.OK, MessageBoxImage.Information);
            await LoadAsync();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning);
        }
    }

    private async void Open_Click(object sender, RoutedEventArgs e)
    {
        if ((sender as FrameworkElement)?.Tag?.ToString() is not { } tag || !int.TryParse(tag, out var id))
            return;
        var dlg = new ContactRequestDetailWindow(id, Session.IsAdmin) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
        if (Session.IsAdmin && dlg.MarkedReadNow)
            await LoadAsync();
    }
}
