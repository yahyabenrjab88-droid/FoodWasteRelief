namespace FoodWasteRelief.Models;

/// <summary>One row in the shopping cart (joined product + business).</summary>
public class CartLine
{
    public int CartItemId { get; set; }
    public int ProduitId { get; set; }
    public string ProduitNom { get; set; } = "";
    public string BusinessName { get; set; } = "";
    public decimal PrixUnitaire { get; set; }
    public int Quantite { get; set; }
    public int StockDisponible { get; set; }
    public decimal SousTotal => Quantite * PrixUnitaire;
}
