using System;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Threading;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Services;
using FoodWasteRelief.Views.Pages;
using MaterialDesignThemes.Wpf;

namespace FoodWasteRelief.Views;

public partial class MainWindow : Window
{
    private readonly DispatcherTimer _clockTimer = new() { Interval = TimeSpan.FromSeconds(1) };
    private readonly DispatcherTimer _badgeTimer = new() { Interval = TimeSpan.FromSeconds(60) };
    private readonly BusinessService _businessService = new();
    private readonly AssociationService _associationService = new();
    private readonly ContactRequestService _contactService = new();

    public MainWindow()
    {
        InitializeComponent();
        Loaded += MainWindow_Loaded;
    }

    private async void MainWindow_Loaded(object sender, RoutedEventArgs e)
    {
        try
        {
            if (!await EnsureSessionInitializedAsync())
                return;

            UserNameText.Text = Session.CurrentUser?.FullName ?? "Utilisateur";
            UserRoleText.Text = (Session.CurrentUser?.Role ?? "").ToUpperInvariant();

            ApplyRoleVisibility();
            UpdateClock();
            _clockTimer.Tick += (_, _) => UpdateClock();
            _clockTimer.Start();

            _badgeTimer.Tick += async (_, _) =>
            {
                try { await UpdatePendingBadgesAsync(); } catch { }
            };
            _badgeTimer.Start();
            await UpdatePendingBadgesAsync();

            if (Session.IsAssociation)
                NavigateTo(new DonationsPage(), "Dons reçus", PackIconKind.HeartOutline);
            else if (Session.IsClient)
                NavigateTo(new ClientCatalogPage(), "Catalogue", PackIconKind.ShoppingSearch);
            else
                NavigateTo(new DashboardPage(), "Tableau de bord", PackIconKind.ViewDashboard);
        }
        catch (Exception ex)
        {
            MessageBox.Show($"Erreur d'ouverture de la fenêtre principale: {ex.Message}", "Erreur", MessageBoxButton.OK, MessageBoxImage.Error);
            Close();
        }
    }

    private async Task<bool> EnsureSessionInitializedAsync()
    {
        if (Session.CurrentUser is null)
        {
            MessageBox.Show("Session utilisateur non initialisee. Veuillez vous reconnecter.", "Session", MessageBoxButton.OK, MessageBoxImage.Warning);
            new LoginWindow().Show();
            Close();
            return false;
        }

        // Shared dependency for role=business pages.
        if (Session.IsBusiness && !Session.LinkedBusinessId.HasValue)
            Session.LinkedBusinessId = await _businessService.GetBusinessIdByUserIdAsync(Session.CurrentUser.Id);
        if (Session.IsAssociation && !Session.LinkedAssociationId.HasValue)
            Session.LinkedAssociationId = await _associationService.GetAssociationIdByUserIdAsync(Session.CurrentUser.Id);

        if (Session.IsBusiness && !Session.LinkedBusinessId.HasValue)
        {
            MessageBox.Show("Aucun etablissement lie a ce compte. Veuillez contacter l'administration.", "Session", MessageBoxButton.OK, MessageBoxImage.Warning);
            Session.Clear();
            new LoginWindow().Show();
            Close();
            return false;
        }

        if (Session.IsAssociation && !Session.LinkedAssociationId.HasValue)
        {
            MessageBox.Show("Aucune association liee a ce compte. Veuillez contacter l'administration.", "Session", MessageBoxButton.OK, MessageBoxImage.Warning);
            Session.Clear();
            new LoginWindow().Show();
            Close();
            return false;
        }

        return true;
    }

    private void ApplyRoleVisibility()
    {
        NavDashboard.Visibility = (Session.IsAssociation || Session.IsClient) ? Visibility.Collapsed : Visibility.Visible;
        NavCatalog.Visibility = Session.IsClient ? Visibility.Visible : Visibility.Collapsed;
        NavCart.Visibility = Session.IsClient ? Visibility.Visible : Visibility.Collapsed;
        NavUsers.Visibility = Session.IsAdmin ? Visibility.Visible : Visibility.Collapsed;
        NavBusinesses.Visibility = Session.IsAdmin ? Visibility.Visible : Visibility.Collapsed;
        NavAssociations.Visibility = Session.IsAdmin ? Visibility.Visible : Visibility.Collapsed;
        NavStatistics.Visibility = Session.IsAdmin ? Visibility.Visible : Visibility.Collapsed;
        NavActivity.Visibility = Visibility.Visible;
        NavProducts.Visibility = (Session.IsAdmin || Session.IsBusiness) ? Visibility.Visible : Visibility.Collapsed;
        NavOrders.Visibility = (Session.IsAdmin || Session.IsBusiness || Session.IsClient) ? Visibility.Visible : Visibility.Collapsed;
        NavDonations.Visibility = (Session.IsAdmin || Session.IsBusiness || Session.IsAssociation) ? Visibility.Visible : Visibility.Collapsed;
        NavStorage.Visibility = Session.IsBusiness ? Visibility.Visible : Visibility.Collapsed;
        NavContact.Visibility = (Session.IsAdmin || Session.IsBusiness || Session.IsClient || Session.IsAssociation)
            ? Visibility.Visible
            : Visibility.Collapsed;
    }

    private void UpdateClock() => ClockText.Text = DateTime.Now.ToString("dddd, dd MMM yyyy • HH:mm:ss");

    private void NavigateTo(object page, string title, PackIconKind icon)
    {
        try
        {
            MainFrame.Content = page;
            PageTitleText.Text = title;
            PageIcon.Kind = icon;
        }
        catch (Exception ex)
        {
            MessageBox.Show($"Navigation error ({title}): {ex.Message}", "Erreur", MessageBoxButton.OK, MessageBoxImage.Error);
        }
    }

    private async Task UpdatePendingBadgesAsync()
    {
        if (!Session.IsAdmin)
            return;

        var pendingBusiness = await _businessService.GetPendingCountAsync();
        var pendingAssoc = await _associationService.GetPendingCountAsync();
        var total = pendingBusiness + pendingAssoc;

        BusinessPendingBadge.Visibility = pendingBusiness > 0 ? Visibility.Visible : Visibility.Collapsed;
        BusinessPendingBadgeText.Text = pendingBusiness.ToString();

        AssocPendingBadge.Visibility = pendingAssoc > 0 ? Visibility.Visible : Visibility.Collapsed;
        AssocPendingBadgeText.Text = pendingAssoc.ToString();

        NotifBadge.Visibility = total > 0 ? Visibility.Visible : Visibility.Collapsed;
        NotifCount.Text = total.ToString();

        var unread = await _contactService.GetUnreadCountAsync();
        ContactUnreadBadge.Visibility = unread > 0 ? Visibility.Visible : Visibility.Collapsed;
        ContactUnreadBadgeText.Text = unread.ToString();
    }

    public async void ShowToast(string title, string message, ToastType type = ToastType.Info, int seconds = 5) =>
        await ToastHost.ShowAsync(title, message, type, seconds);

    private void NavDashboard_Click(object sender, RoutedEventArgs e) => NavigateTo(new DashboardPage(), "Tableau de bord", PackIconKind.ViewDashboard);
    private void NavUsers_Click(object sender, RoutedEventArgs e) => NavigateTo(new UsersPage(), "Utilisateurs", PackIconKind.AccountGroup);
    private void NavBusinesses_Click(object sender, RoutedEventArgs e) => NavigateTo(new BusinessesPage(), "Etablissements", PackIconKind.Storefront);
    private void NavAssociations_Click(object sender, RoutedEventArgs e) => NavigateTo(new AssociationsPage(), "Associations", PackIconKind.HandHeart);
    private void NavProducts_Click(object sender, RoutedEventArgs e) => NavigateTo(new ProductsPage(), "Produits", PackIconKind.FoodApple);
    private void NavOrders_Click(object sender, RoutedEventArgs e) =>
        NavigateTo(new OrdersPage(), Session.IsClient ? "Mes commandes" : "Commandes", PackIconKind.CartOutline);

    private void NavCatalog_Click(object sender, RoutedEventArgs e) =>
        NavigateTo(new ClientCatalogPage(), "Catalogue", PackIconKind.ShoppingSearch);

    private void NavCart_Click(object sender, RoutedEventArgs e) =>
        NavigateTo(new CartPage(), "Mon panier", PackIconKind.Cart);
    private void NavDonations_Click(object sender, RoutedEventArgs e) => NavigateTo(new DonationsPage(), "Dons", PackIconKind.HeartOutline);
    private void NavStorage_Click(object sender, RoutedEventArgs e) => NavigateTo(new StoragePage(), "Stockage", PackIconKind.ArchiveOutline);
    private void NavContact_Click(object sender, RoutedEventArgs e) => NavigateTo(new ContactRequestsPage(), "Contact", PackIconKind.EmailOutline);
    private void NavActivity_Click(object sender, RoutedEventArgs e) =>
        NavigateTo(new ActivityLogPage(), Session.IsAdmin ? "Journal d'activité" : "Mon activité", PackIconKind.History);

    private void NavStatistics_Click(object sender, RoutedEventArgs e) => NavigateTo(new StatisticsPage(), "Statistiques", PackIconKind.ChartBar);

    private void Logout_Click(object sender, RoutedEventArgs e)
    {
        Session.Clear();
        new LoginWindow().Show();
        Close();
    }
}

