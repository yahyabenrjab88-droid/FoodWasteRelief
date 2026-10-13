namespace FoodWasteRelief.Models;

public class CommandeDetail
{
    public int Id { get; set; }
    public int CommandeId { get; set; }
    public int ProduitId { get; set; }
    public int Quantite { get; set; }
    public decimal PrixUnitaire { get; set; }
    public string ProduitNom { get; set; } = "";
    public decimal Total => Quantite * PrixUnitaire;
}

