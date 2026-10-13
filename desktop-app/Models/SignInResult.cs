namespace FoodWasteRelief.Models;

/// <summary>Result of application sign-in (email + password).</summary>
public sealed class SignInResult
{
    public bool IsSuccess { get; }
    public User? User { get; }
    /// <summary>Empty when successful; otherwise a message for the user.</summary>
    public string ErrorMessage { get; }

    private SignInResult(bool success, User? user, string errorMessage)
    {
        IsSuccess = success;
        User = user;
        ErrorMessage = errorMessage ?? "";
    }

    public static SignInResult Ok(User user) => new(true, user, "");

    public static SignInResult Fail(string message) => new(false, null, message);
}
