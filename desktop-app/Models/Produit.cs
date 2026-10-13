using System;

namespace FoodWasteRelief.Models;

public class Produit
{
    public int Id { get; set; }
    public int BusinessId { get; set; }
    public int? StorageItemId { get; set; }
    public string Nom { get; set; } = "";
    public string Description { get; set; } = "";
    public string Categorie { get; set; } = "";
    public decimal Prix { get; set; }
    public int Quantite { get; set; }
    public DateTime DateExpiration { get; set; }
    public string Type { get; set; } = "vente"; // "vente", "don"
    public string? ImageUrl { get; set; }
    public bool IsActive { get; set; } = true;

    public string BusinessName { get; set; } = "";

    public string EtatAffiche => IsActive ? "Actif" : "Inactif";

    /// <summary>Row action visibility (not persisted).</summary>
    public bool ShowRetireProduct { get; set; }

    public bool IsExpiringSoon => DateExpiration.Date <= DateTime.Today.AddDays(3);
    public bool IsExpired => DateExpiration.Date < DateTime.Today;
    public bool IsLowStock => Quantite < 5;
}
