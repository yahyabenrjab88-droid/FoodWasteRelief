using System;
using System.Globalization;
using System.Linq;
using System.Windows;
using System.Windows.Data;

namespace FoodWasteRelief.Helpers;

/// <summary>
/// Parameter: "admin" or "admin,business"
/// </summary>
public class RoleToVisibilityConverter : IValueConverter
{
    public object Convert(object value, Type targetType, object parameter, CultureInfo culture)
    {
        var role = (value?.ToString() ?? Session.CurrentUser?.Role ?? "").Trim().ToLowerInvariant();
        var allowed = (parameter?.ToString() ?? "")
            .Split(',', StringSplitOptions.TrimEntries | StringSplitOptions.RemoveEmptyEntries)
            .Select(r => r.ToLowerInvariant())
            .ToArray();

        if (allowed.Length == 0)
            return Visibility.Collapsed;

        return allowed.Contains(role) ? Visibility.Visible : Visibility.Collapsed;
    }

    public object ConvertBack(object value, Type targetType, object parameter, CultureInfo culture) =>
        Binding.DoNothing;
}

