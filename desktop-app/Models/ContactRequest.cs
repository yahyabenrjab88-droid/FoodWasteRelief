using System;

namespace FoodWasteRelief.Models;

public class ContactRequest
{
    public int Id { get; set; }
    public int SenderId { get; set; }
    public string SenderRole { get; set; } = "";
    public string SenderName { get; set; } = "";
    public string Subject { get; set; } = "";
    public string Body { get; set; } = "";
    public DateTime SentAt { get; set; }
    public bool IsRead { get; set; }
}
