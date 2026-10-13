using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using FoodWasteRelief.Dialogs;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;
using FoodWasteRelief.Views.Details;

namespace FoodWasteRelief.Views.Pages;

public partial class CartPage : Page
{
    private readonly CartService _cart = new();
    private readonly OrderService _orders = new();

    public CartPage()
    {
        InitializeComponent();
        Loaded += async (_, _) => await LoadAsync();
    }

    private int ClientId => Session.CurrentUser?.Id ?? 0;

    private async Task LoadAsync()
    {
        if (!Session.IsClient || ClientId == 0)
        {
            GridCart.ItemsSource = new List<CartLine>();
            TotalText.Text = "Total : 0,00 TND";
            return;
        }

        try
        {
            var lines = await _cart.GetLinesAsync(ClientId);
            GridCart.ItemsSource = lines;
            var total = lines.Sum(l => l.SousTotal);
            TotalText.Text = $"Total : {total:F2} TND";
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Panier", ex.Message);
        }
    }

    private async void RefreshBtn_Click(object sender, RoutedEventArgs e) => await LoadAsync();

    private CartLine? FindLine(int cartItemId) =>
        (GridCart.ItemsSource as IEnumerable<CartLine>)?.FirstOrDefault(l => l.CartItemId == cartItemId);

    private async void Minus_Click(object sender, RoutedEventArgs e)
    {
        if (sender is not Button b || !int.TryParse(b.Tag?.ToString(), out var cartId)) return;
        var line = FindLine(cartId);
        if (line is null) return;
        try
        {
            await _cart.SetLineQuantityAsync(ClientId, cartId, line.Quantite - 1);
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Panier", ex.Message);
        }
    }

    private async void Plus_Click(object sender, RoutedEventArgs e)
    {
        if (sender is not Button b || !int.TryParse(b.Tag?.ToString(), out var cartId)) return;
        var line = FindLine(cartId);
        if (line is null) return;
        try
        {
            await _cart.SetLineQuantityAsync(ClientId, cartId, line.Quantite + 1);
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Panier", ex.Message);
        }
    }

    private async void Remove_Click(object sender, RoutedEventArgs e)
    {
        if (sender is not Button b || !int.TryParse(b.Tag?.ToString(), out var cartId)) return;
        if (!ConfirmDialog.Show(Window.GetWindow(this), "Retirer du panier", "Retirer cette ligne du panier ?", destructive: true, "Retirer"))
            return;
        try
        {
            await _cart.RemoveLineAsync(ClientId, cartId);
            ToastHelper.Info("Panier", "Ligne retirée.");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Panier", ex.Message);
        }
    }

    private async void Checkout_Click(object sender, RoutedEventArgs e)
    {
        if (!Session.IsClient || ClientId == 0)
        {
            ToastHelper.Error("Session", "Connexion client requise.");
            return;
        }

        if (!ConfirmDialog.Show(Window.GetWindow(this), "Commander", "Valider votre panier et créer vos commandes ?", false, "Commander"))
            return;

        try
        {
            var n = await _orders.CheckoutCartAsync(ClientId);
            await ActivityLogHelper.LogActivityAsync(
                ClientId,
                Session.CurrentUser?.Role ?? "client",
                Session.CurrentUser?.FullName ?? "",
                "Validation du panier",
                $"{n} commande(s) créée(s).");
            ToastHelper.Success("Commande", n <= 1 ? "Votre commande a été enregistrée." : $"{n} commandes ont été enregistrées (par établissement).");
            await LoadAsync();
        }
        catch (Exception ex)
        {
            ToastHelper.Error("Commande", ex.Message);
        }
    }

    private async void ViewProduct_Click(object sender, RoutedEventArgs e)
    {
        if (sender is not Button b || !int.TryParse(b.Tag?.ToString(), out var productId)) return;
        var dlg = new ProductDetailWindow(productId) { Owner = Window.GetWindow(this) };
        dlg.ShowDialog();
        if (dlg.AddedToCart)
            ToastHelper.Success("Panier", "Article ajouté au panier.");
        await LoadAsync();
    }
}
