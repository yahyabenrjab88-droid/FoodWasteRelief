using System.Windows;
using FoodWasteRelief.Views;

namespace FoodWasteRelief.Helpers;

public static class ToastHelper
{
    public static void Success(string title, string message) =>
        TryShow(m => m.ShowToast(title, message, ToastType.Success, 3));

    public static void Error(string title, string message) =>
        TryShow(m => m.ShowToast(title, message, ToastType.Danger, 4));

    public static void Info(string title, string message) =>
        TryShow(m => m.ShowToast(title, message, ToastType.Info, 3));

    private static void TryShow(System.Action<MainWindow> action)
    {
        if (Application.Current?.MainWindow is MainWindow mw)
            action(mw);
    }
}
