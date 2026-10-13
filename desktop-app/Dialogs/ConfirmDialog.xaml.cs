using System.Windows;
using MaterialDesignThemes.Wpf;

namespace FoodWasteRelief.Dialogs;

public partial class ConfirmDialog : Window
{
    public bool Result { get; private set; }

    private ConfirmDialog()
    {
        InitializeComponent();
    }

    /// <summary>Shows a styled confirm dialog. Returns true if the user confirmed.</summary>
    public static bool Show(Window? owner, string title, string message, bool destructive = false, string confirmText = "Confirmer")
    {
        var dlg = new ConfirmDialog();
        dlg.Title = title;
        dlg.TitleText.Text = title;
        dlg.MessageText.Text = message;
        dlg.ConfirmButton.Content = confirmText;

        if (destructive)
        {
            var danger = dlg.TryFindResource("DangerButton") as Style
                         ?? Application.Current.TryFindResource("DangerButton") as Style;
            if (danger is not null)
                dlg.ConfirmButton.Style = danger;
            dlg.TitleIcon.Kind = PackIconKind.AlertCircleOutline;
        }

        dlg.Owner = owner ?? Application.Current.MainWindow;
        var dialogResult = dlg.ShowDialog();
        return dialogResult == true && dlg.Result;
    }

    private void ConfirmButton_Click(object sender, RoutedEventArgs e)
    {
        Result = true;
        DialogResult = true;
        Close();
    }

    private void CancelButton_Click(object sender, RoutedEventArgs e)
    {
        Result = false;
        DialogResult = false;
        Close();
    }
}
