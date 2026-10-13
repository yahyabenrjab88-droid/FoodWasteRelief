using System.Threading.Tasks;
using System.Windows;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using FoodWasteRelief.Services;

namespace FoodWasteRelief.Views;

public partial class LoginWindow : Window
{
    private readonly AuthService _auth = new();
    private readonly BusinessService _businessService = new();
    private readonly AssociationService _associationService = new();

    public LoginWindow()
    {
        InitializeComponent();

        EmailBox.Text = Properties.Settings.Default.RememberedEmail ?? "";
        RememberMeBox.IsChecked = !string.IsNullOrWhiteSpace(EmailBox.Text);
    }

    private async void BtnLogIn_Click(object sender, RoutedEventArgs e)
    {
        ClearSignInError();
        SetLogInBusy(true);

        try
        {
            var email = (EmailBox.Text ?? "").Trim();
            var pass = PasswordBox.Password ?? "";

            var result = await _auth.LogInAsync(email, pass);
            if (!result.IsSuccess)
            {
                ShowLogInError(result.ErrorMessage);
                return;
            }

            await NavigateAfterSuccessAsync(result.User!, RememberMeBox.IsChecked == true, email);
        }
        finally
        {
            SetLogInBusy(false);
        }
    }

    private async void BtnSignInNew_Click(object sender, RoutedEventArgs e)
    {
        ClearLogInError();
        SetSignInBusy(true);

        try
        {
            var role =
                RoleBusinessRadio.IsChecked == true ? "business" :
                RoleAssociationRadio.IsChecked == true ? "association" :
                "client";
            var result = await _auth.RegisterAsync(
                RegEmailBox.Text ?? "",
                RegPasswordBox.Password ?? "",
                RegConfirmPasswordBox.Password ?? "",
                RegNomBox.Text ?? "",
                RegPrenomBox.Text ?? "",
                RegTelephoneBox.Text ?? "",
                role);

            if (!result.IsSuccess)
            {
                ShowSignInError(result.ErrorMessage);
                return;
            }

            await NavigateAfterSuccessAsync(result.User!, false, "");
        }
        finally
        {
            SetSignInBusy(false);
        }
    }

    private async Task NavigateAfterSuccessAsync(User user, bool remember, string emailForRemember)
    {
        MainWindow? main = null;
        try
        {
            Session.CurrentUser = user;
            Session.BusinessId = null;
            Session.AssociationId = null;

            if (Session.IsBusiness)
                Session.BusinessId = await _businessService.GetBusinessIdByUserIdAsync(user.Id);
            if (Session.IsAssociation)
                Session.AssociationId = await _associationService.GetAssociationIdByUserIdAsync(user.Id);

            if (remember && !string.IsNullOrWhiteSpace(emailForRemember))
                Properties.Settings.Default.RememberedEmail = emailForRemember;
            else if (!remember)
                Properties.Settings.Default.RememberedEmail = "";
            Properties.Settings.Default.Save();

            main = new MainWindow();
            main.Show();
            await ActivityLogHelper.LogActivityAsync(user.Id, user.Role, user.FullName, "Login", "User logged in successfully.");
            Close();
        }
        catch (System.Exception ex)
        {
            main?.Close();
            Session.Clear();
            ShowLogInError("Cannot open main window: " + ex.Message);
        }
    }

    private void SetLogInBusy(bool busy)
    {
        MainAccountTabs.IsEnabled = !busy;
        BtnLogIn.IsEnabled = !busy;
        BtnSignInNew.IsEnabled = !busy;
        LogInProgress.Visibility = busy ? Visibility.Visible : Visibility.Collapsed;
        BtnLogInLabel.Visibility = busy ? Visibility.Collapsed : Visibility.Visible;
    }

    private void SetSignInBusy(bool busy)
    {
        MainAccountTabs.IsEnabled = !busy;
        BtnLogIn.IsEnabled = !busy;
        BtnSignInNew.IsEnabled = !busy;
        SignInProgress.Visibility = busy ? Visibility.Visible : Visibility.Collapsed;
        BtnSignInNewLabel.Visibility = busy ? Visibility.Collapsed : Visibility.Visible;
    }

    private void ShowLogInError(string message)
    {
        LogInErrorText.Text = message;
        LogInErrorText.Visibility = Visibility.Visible;
    }

    private void ShowSignInError(string message)
    {
        SignInErrorText.Text = message;
        SignInErrorText.Visibility = Visibility.Visible;
    }

    private void ClearLogInError()
    {
        LogInErrorText.Visibility = Visibility.Collapsed;
        LogInErrorText.Text = "";
    }

    private void ClearSignInError()
    {
        SignInErrorText.Visibility = Visibility.Collapsed;
        SignInErrorText.Text = "";
    }
}
