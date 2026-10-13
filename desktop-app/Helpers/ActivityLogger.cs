using System.Threading.Tasks;

namespace FoodWasteRelief.Helpers;

/// <summary>Writes one row to activity_logs (non-blocking on failure).</summary>
public static class ActivityLogger
{
    public static Task Log(int userId, string role, string username, string action, string details) =>
        ActivityLogHelper.LogActivityAsync(userId, role, username, action, details);
}
