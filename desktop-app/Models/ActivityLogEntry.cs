using System;

namespace FoodWasteRelief.Models;

public class ActivityLogEntry
{
    public DateTime CreatedAt { get; set; }
    public string Username { get; set; } = "";
    public string UserRole { get; set; } = "";
    public string Action { get; set; } = "";
    public string Details { get; set; } = "";
}
