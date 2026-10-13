using System;
using System.Globalization;
using System.Windows.Data;

namespace FoodWasteRelief.Helpers;

public class StatusToTextConverter : IValueConverter
{
    public object Convert(object value, Type targetType, object parameter, CultureInfo culture)
    {
        var status = (value?.ToString() ?? string.Empty).Trim().ToLowerInvariant();
        return status switch
        {
            "en_attente" => "En attente",
            "valide" => "Valide",
            "confirmee" => "Confirmee",
            "refuse" => "Refuse",
            "livree" => "Livree",
            "vente" => "Vente",
            "don" => "Don",
            _ => status
        };
    }

    public object ConvertBack(object value, Type targetType, object parameter, CultureInfo culture) => Binding.DoNothing;
}
