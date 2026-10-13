using FoodWasteRelief.Models;

namespace FoodWasteRelief.Models;

public class Association
{
    public int Id { get; set; }
    public int UserId { get; set; }
    public string Nom { get; set; } = "";
    public string Ville { get; set; } = "";
    public string Telephone { get; set; } = "";
    public string Status { get; set; } = "en_attente";
    public User? Owner { get; set; }
}

