using FoodWasteRelief.Models;

namespace FoodWasteRelief.Models;

public class Business
{
    public int Id { get; set; }
    public int UserId { get; set; }
    public string NomBusiness { get; set; } = "";
    public string Localisation { get; set; } = "";
    public string Ville { get; set; } = "";
    public string Patente { get; set; } = "";
    public string Status { get; set; } = "en_attente"; // "en_attente", "valide", "refuse"
    public User? Owner { get; set; }
}

