using System;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Media;

namespace FoodWasteRelief.Controls;

public partial class StatusBadge : UserControl
{
    public static readonly DependencyProperty StatusProperty = DependencyProperty.Register(
        nameof(Status),
        typeof(string),
        typeof(StatusBadge),
        new PropertyMetadata("", OnStatusChanged));

    public string Status
    {
        get => (string)GetValue(StatusProperty);
        set => SetValue(StatusProperty, value);
    }

    public StatusBadge()
    {
        InitializeComponent();
        ApplyPalette(Status);
    }

    private static void OnStatusChanged(DependencyObject d, DependencyPropertyChangedEventArgs e)
    {
        if (d is StatusBadge badge)
            badge.ApplyPalette(e.NewValue as string ?? "");
    }

    private void ApplyPalette(string raw)
    {
        var s = (raw ?? "").Trim().ToLowerInvariant();
        Brush bg;
        Brush fg;

        if (s.Contains("rejet") || s.Contains("reject") || s.Contains("refus") || s.Contains("deactiv") || s.Contains("désactiv")
            || s.Contains("annul"))
        {
            bg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#F8D7DA")!);
            fg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#842029")!);
        }
        else if (s.Contains("cours") || s.Contains("livraison") || s.Contains("expédi"))
        {
            bg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#CCE5FF")!);
            fg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#004085")!);
        }
        else if (s.Contains("pending") || s.Contains("en_attente") || s.Contains("attente"))
        {
            bg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#FFF3CD")!);
            fg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#856404")!);
        }
        else if (s.Contains("inactif"))
        {
            bg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#E2E3E5")!);
            fg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#383D41")!);
        }
        else if (s.Length == 0)
        {
            bg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#E2E3E5")!);
            fg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#383D41")!);
        }
        else
        {
            bg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#D1E7DD")!);
            fg = new SolidColorBrush((Color)ColorConverter.ConvertFromString("#0A3622")!);
        }

        BadgeBorder.Background = bg;
        BadgeText.Foreground = fg;
        BadgeText.Text = string.IsNullOrWhiteSpace(raw) ? "—" : raw;
    }
}
