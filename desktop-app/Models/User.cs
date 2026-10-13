using System;
using System.Linq;

namespace FoodWasteRelief.Models;

public class User
{
    public int Id { get; set; }
    public string Email { get; set; } = "";
    public string Password { get; set; } = "";
    public string Role { get; set; } = "client"; // "client", "business", "admin"
    public string Nom { get; set; } = "";
    public string Prenom { get; set; } = "";
    public string Telephone { get; set; } = "";
    public DateTime CreatedAt { get; set; }

    public string FullName => $"{Prenom} {Nom}".Trim();

    public string Initials
    {
        get
        {
            var first = string.IsNullOrWhiteSpace(Prenom) ? '\0' : Prenom.Trim().FirstOrDefault();
            var last = string.IsNullOrWhiteSpace(Nom) ? '\0' : Nom.Trim().FirstOrDefault();
            var s = new string(new[] { first, last }.Where(c => c != '\0').ToArray());
            return string.IsNullOrWhiteSpace(s) ? "?" : s.ToUpperInvariant();
        }
    }
}

