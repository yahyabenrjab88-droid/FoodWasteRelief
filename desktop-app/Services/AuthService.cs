using System;
using System.Threading.Tasks;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

/// <summary>Log in (existing users) and register / sign in (new Client or Business users only).</summary>
public class AuthService
{
    private readonly UserService _users = new();
    private readonly BusinessService _business = new();
    private readonly AssociationService _associations = new();

    /// <summary>Existing account — email + password only.</summary>
    public async Task<SignInResult> LogInAsync(string email, string password)
    {
        var e = (email ?? "").Trim();
        var p = password ?? "";

        if (string.IsNullOrWhiteSpace(e))
            return SignInResult.Fail("Enter your email.");
        if (string.IsNullOrWhiteSpace(p))
            return SignInResult.Fail("Enter your password.");

        try
        {
            var existing = await _users.GetByEmailAsync(e);
            if (existing is null)
                return SignInResult.Fail("No account for this email. Use the Sign in tab to create one (Client or Business).");

            var user = await _users.AuthenticateAsync(e, p);
            if (user is null)
                return SignInResult.Fail("Wrong password.");
            return SignInResult.Ok(user);
        }
        catch (Exception ex)
        {
            return SignInResult.Fail("Cannot reach the database. Start MySQL (XAMPP) and check the connection. " + ex.Message);
        }
    }

    /// <summary>New account — role must be <c>client</c> or <c>business</c> (admin is not self-service).</summary>
    public async Task<SignInResult> RegisterAsync(
        string email,
        string password,
        string confirmPassword,
        string nom,
        string prenom,
        string telephone,
        string role)
    {
        var r = (role ?? "").Trim().ToLowerInvariant();
        if (r != "client" && r != "business" && r != "association")
            return SignInResult.Fail("Choose Client, Business, or Association. Admin accounts are created by an administrator.");

        var e = (email ?? "").Trim();
        var p = password ?? "";
        var p2 = confirmPassword ?? "";
        var n = (nom ?? "").Trim();
        var pr = (prenom ?? "").Trim();
        var t = (telephone ?? "").Trim();

        if (string.IsNullOrWhiteSpace(e))
            return SignInResult.Fail("Enter your email.");
        if (string.IsNullOrWhiteSpace(p))
            return SignInResult.Fail("Choose a password.");
        if (!string.Equals(p, p2, StringComparison.Ordinal))
            return SignInResult.Fail("Passwords do not match.");
        if (string.IsNullOrWhiteSpace(n) || string.IsNullOrWhiteSpace(pr) || string.IsNullOrWhiteSpace(t))
            return SignInResult.Fail("Fill in first name, last name, and phone.");

        try
        {
            var existing = await _users.GetByEmailAsync(e);
            if (existing is not null)
                return SignInResult.Fail("This email is already registered. Use the Log in tab.");

            var user = new User
            {
                Email = e,
                Role = r,
                Nom = n,
                Prenom = pr,
                Telephone = t
            };

            var newId = await _users.CreateAsync(user, p);
            user.Id = newId;

            if (r == "business")
                await _business.CreatePendingBusinessForUserAsync(newId);
            else if (r == "association")
                await _associations.CreatePendingAssociationForUserAsync(newId);

            var fresh = await _users.GetByEmailAsync(e);
            return fresh is null
                ? SignInResult.Fail("Account created but could not load profile. Try Log in.")
                : SignInResult.Ok(fresh);
        }
        catch (MySqlException ex) when (ex.Number == 1062)
        {
            return SignInResult.Fail("This email is already in use. Use Log in.");
        }
        catch (Exception ex)
        {
            return SignInResult.Fail("Registration failed. " + ex.Message);
        }
    }

}
