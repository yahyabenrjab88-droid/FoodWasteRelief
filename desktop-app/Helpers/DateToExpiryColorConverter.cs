using System;
using System.Globalization;
using System.Windows.Data;
using System.Windows.Media;

namespace FoodWasteRelief.Helpers;

public class DateToExpiryColorConverter : IValueConverter
{
    public object Convert(object value, Type targetType, object parameter, CultureInfo culture)
    {
        if (value is not DateTime date)
            return new SolidColorBrush((Color)ColorConverter.ConvertFromString("#9E9E9E"));

        if (date.Date < DateTime.Today)
            return new SolidColorBrush((Color)ColorConverter.ConvertFromString("#E63946"));
        if (date.Date <= DateTime.Today.AddDays(3))
            return new SolidColorBrush((Color)ColorConverter.ConvertFromString("#E9C46A"));
        return new SolidColorBrush((Color)ColorConverter.ConvertFromString("#52B788"));
    }

    public object ConvertBack(object value, Type targetType, object parameter, CultureInfo culture) => Binding.DoNothing;
}
