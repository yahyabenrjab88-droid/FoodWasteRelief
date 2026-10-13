using System;
using System.Globalization;
using System.Windows.Data;
using System.Windows.Media;

namespace FoodWasteRelief.Helpers;

public class StatusToColorConverter : IValueConverter
{
    public object Convert(object value, Type targetType, object parameter, CultureInfo culture)
    {
        var status = (value?.ToString() ?? "").Trim().ToLowerInvariant();

        var color = status switch
        {
            "en_attente" => (Color)ColorConverter.ConvertFromString("#E9C46A"),
            "valide" => (Color)ColorConverter.ConvertFromString("#52B788"),
            "confirmee" => (Color)ColorConverter.ConvertFromString("#52B788"),
            "refuse" => (Color)ColorConverter.ConvertFromString("#E63946"),
            "livree" => (Color)ColorConverter.ConvertFromString("#2D6A4F"),
            "vente" => (Color)ColorConverter.ConvertFromString("#F4845F"),
            "don" => (Color)ColorConverter.ConvertFromString("#52B788"),
            _ => (Color)ColorConverter.ConvertFromString("#9E9E9E")
        };
        return new SolidColorBrush(color);
    }

    public object ConvertBack(object value, Type targetType, object parameter, CultureInfo culture) =>
        Binding.DoNothing;
}

