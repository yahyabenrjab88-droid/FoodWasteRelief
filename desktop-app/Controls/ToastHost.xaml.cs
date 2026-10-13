using System;
using System.Threading;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Media;
using System.Windows.Media.Animation;
using FoodWasteRelief.Helpers;
using MaterialDesignThemes.Wpf;

namespace FoodWasteRelief.Controls;

public partial class ToastHost : System.Windows.Controls.UserControl
{
    private readonly SemaphoreSlim _gate = new(1, 1);

    public ToastHost()
    {
        InitializeComponent();
    }

    public async Task ShowAsync(string title, string message, ToastType type = ToastType.Info, int seconds = 5)
    {
        await _gate.WaitAsync();
        try
        {
            ToastTitle.Text = title;
            ToastMessage.Text = message;
            ToastIcon.Kind = type switch
            {
                ToastType.Success => PackIconKind.CheckCircleOutline,
                ToastType.Warning => PackIconKind.AlertOutline,
                ToastType.Danger => PackIconKind.AlertCircleOutline,
                _ => PackIconKind.InformationOutline
            };
            ToastRoot.Background = new SolidColorBrush((Color)ColorConverter.ConvertFromString(type switch
            {
                ToastType.Success => "#2D6A4F",
                ToastType.Warning => "#92600A",
                ToastType.Danger => "#C42030",
                _ => "#1B2D23"
            })!);
            ToastRoot.Visibility = Visibility.Visible;

            var slideIn = new DoubleAnimation(80, 0, TimeSpan.FromMilliseconds(300))
            {
                EasingFunction = new CubicEase { EasingMode = EasingMode.EaseOut }
            };
            ToastTranslate.BeginAnimation(TranslateTransform.YProperty, slideIn);

            await Task.Delay(TimeSpan.FromSeconds(seconds));

            var slideOut = new DoubleAnimation(0, 80, TimeSpan.FromMilliseconds(300))
            {
                EasingFunction = new CubicEase { EasingMode = EasingMode.EaseIn }
            };
            slideOut.Completed += (_, _) => ToastRoot.Visibility = Visibility.Collapsed;
            ToastTranslate.BeginAnimation(TranslateTransform.YProperty, slideOut);

            await Task.Delay(TimeSpan.FromMilliseconds(350));
        }
        finally
        {
            _gate.Release();
        }
    }
}
