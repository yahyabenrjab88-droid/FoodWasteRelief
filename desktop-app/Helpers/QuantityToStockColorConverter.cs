using System;
using System.Globalization;
using System.Windows.Data;
using System.Windows.Media;

namespace FoodWasteRelief.Helpers;

public class QuantityToStockColorConverter : IValueConverter
{
    public object Convert(object value, Type targetType, object parameter, CultureInfo culture)
    {
        var qty = 0;
        if (value is int i)
            qty = i;
        else if (int.TryParse(value?.ToString(), out var parsed))
            qty = parsed;

        return qty switch
        {
            <= 0 => new SolidColorBrush((Color)ColorConverter.ConvertFromString("#E63946")),
            < 5 => new SolidColorBrush((Color)ColorConverter.ConvertFromString("#E9C46A")),
            _ => new SolidColorBrush((Color)ColorConverter.ConvertFromString("#52B788"))
        };
    }

    public object ConvertBack(object value, Type targetType, object parameter, CultureInfo culture) => Binding.DoNothing;
}
