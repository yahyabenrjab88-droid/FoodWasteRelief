using System;
using System.Configuration;
using System.IO;
using System.Net.Http;
using System.Text.Json;
using System.Threading.Tasks;

namespace FoodWasteRelief.Helpers;

public static class CloudinaryHelper
{
    public static async Task<string> UploadImageAsync(string filePath)
    {
        if (string.IsNullOrWhiteSpace(filePath) || !File.Exists(filePath))
            throw new FileNotFoundException("Image file not found.", filePath);

        var cloudName = ConfigurationManager.AppSettings["CloudinaryCloudName"] ?? "";
        var uploadPreset = ConfigurationManager.AppSettings["CloudinaryUploadPreset"] ?? "";
        if (string.IsNullOrWhiteSpace(cloudName) || string.IsNullOrWhiteSpace(uploadPreset))
            throw new Exception("Cloudinary settings are missing in App.config.");

        var url = $"https://api.cloudinary.com/v1_1/{cloudName}/image/upload";
        using var http = new HttpClient();
        using var form = new MultipartFormDataContent();
        form.Add(new StringContent(uploadPreset), "upload_preset");

        await using var stream = File.OpenRead(filePath);
        using var fileContent = new StreamContent(stream);
        form.Add(fileContent, "file", Path.GetFileName(filePath));

        var response = await http.PostAsync(url, form);
        var body = await response.Content.ReadAsStringAsync();
        if (!response.IsSuccessStatusCode)
            throw new Exception("Cloudinary upload failed: " + body);

        using var doc = JsonDocument.Parse(body);
        if (!doc.RootElement.TryGetProperty("secure_url", out var secureUrlNode))
            throw new Exception("Cloudinary response did not contain secure_url.");

        return secureUrlNode.GetString() ?? throw new Exception("Cloudinary secure_url was empty.");
    }
}
