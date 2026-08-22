using System.Text.Json;
using System.Text.Json.Serialization;
using RetexaPrintBridge;
var cfg = new BridgeConfig();

var builder = WebApplication.CreateBuilder(args);

builder.Configuration
    .GetSection("Bridge")
    .Bind(cfg);

builder.WebHost.UseUrls($"http://{cfg.Host}:{cfg.Port}");

var app = builder.Build();

app.MapGet("/", () =>
    Results.Ok(new
    {
        name = "RetexaPrintBridge",
        status = "ok",
        listen = $"http://{cfg.Host}:{cfg.Port}"
    })
);

app.MapGet("/health", () =>
    Results.Ok(new
    {
        status = "ok",
        printer = cfg.AllowedPrinters.FirstOrDefault() ?? ""
    })
);

app.MapGet("/printers", () =>
    Results.Ok(new
    {
        printers = cfg.AllowedPrinters
    })
);

app.MapPost("/print", async (
    HttpRequest request,
    ILoggerFactory lf) =>
{
    var log = lf.CreateLogger("Print");

    try
    {
        // ============================================
        // TEMPORARY DIAGNOSTIC LOGGING
        // ============================================

        Console.WriteLine();
        Console.WriteLine("============================================");
        Console.WriteLine("PRINT REQUEST RECEIVED");
        Console.WriteLine("============================================");

        request.EnableBuffering();

        using var reader = new StreamReader(request.Body);

        var rawBody = await reader.ReadToEndAsync();

        request.Body.Position = 0;

        Console.WriteLine("RAW REQUEST:");
        Console.WriteLine(rawBody);

        Console.WriteLine("============================================");
        Console.WriteLine();

        // ============================================
        // DESERIALIZE PRINT JOB
        // ============================================

        var job = await JsonSerializer.DeserializeAsync<PrintJob>(
            request.Body,
            new JsonSerializerOptions
            {
                PropertyNameCaseInsensitive = true
            });

        if (job is null)
        {
            Console.WriteLine("ERROR: Invalid JSON.");

            return Results.BadRequest(new
            {
                success = false,
                message = "Invalid JSON."
            });
        }

        Console.WriteLine($"Printer : {job.Printer}");
        Console.WriteLine($"Job ID  : {job.JobId}");
        Console.WriteLine($"Copies  : {job.Copies}");
        Console.WriteLine($"Data    : {(string.IsNullOrWhiteSpace(job.Data) ? "(empty)" : $"Base64 length = {job.Data.Length}")}");

        // ============================================
        // CHECK PRINTER
        // ============================================

        if (!cfg.AllowedPrinters.Contains(
            job.Printer,
            StringComparer.OrdinalIgnoreCase))
        {
            Console.WriteLine(
                $"ERROR: Printer '{job.Printer}' is not allowed."
            );

            return Results.BadRequest(new
            {
                success = false,
                message = $"Printer '{job.Printer}' is not in the allowed list."
            });
        }

        // ============================================
        // CHECK COPIES
        // ============================================

        if (job.Copies < 1 || job.Copies > 10)
        {
            Console.WriteLine(
                $"ERROR: Invalid copies value: {job.Copies}"
            );

            return Results.BadRequest(new
            {
                success = false,
                message = "Copies must be between 1 and 10."
            });
        }

        // ============================================
        // CHECK JOB ID
        // ============================================

        if (string.IsNullOrWhiteSpace(job.JobId))
        {
            Console.WriteLine("ERROR: job_id is empty.");

            return Results.BadRequest(new
            {
                success = false,
                message = "job_id is required."
            });
        }

        // ============================================
        // CHECK DATA
        // ============================================

        if (string.IsNullOrWhiteSpace(job.Data))
        {
            Console.WriteLine("ERROR: data is empty.");

            return Results.BadRequest(new
            {
                success = false,
                message = "data is required."
            });
        }

        // ============================================
        // DECODE BASE64
        // ============================================

        byte[] data;

        try
        {
            data = Convert.FromBase64String(job.Data);
        }
        catch
        {
            Console.WriteLine("ERROR: Invalid Base64 data.");

            return Results.BadRequest(new
            {
                success = false,
                message = "data must be valid Base64."
            });
        }

        // ============================================
        // CHECK SIZE
        // ============================================

        if (data.Length > cfg.MaxBytes)
        {
            Console.WriteLine(
                $"ERROR: Print job too large: {data.Length} bytes."
            );

            return Results.BadRequest(new
            {
                success = false,
                message = "Print job is too large."
            });
        }

        // ============================================
        // PRINT
        // ============================================

        log.LogInformation(
            "PRINT REQUEST printer={Printer} job={Job} bytes={Bytes}",
            job.Printer,
            job.JobId,
            data.Length
        );

        if (!OperatingSystem.IsWindows())
        {
            Console.WriteLine("ERROR: Not running on Windows.");

            return Results.StatusCode(503);
        }

        for (var i = 0; i < job.Copies; i++)
        {
            Console.WriteLine(
                $"Sending print job to: {job.Printer}"
            );

            RawPrinter.Send(job.Printer, data);
        }

        log.LogInformation(
            "PRINT SUCCESS job={Job}",
            job.JobId
        );

        Console.WriteLine(
            $"PRINT SUCCESS: {job.JobId}"
        );

        return Results.Ok(new
        {
            success = true,
            job_id = job.JobId,
            printer = job.Printer,
            bytes = data.Length,
            copies = job.Copies
        });
    }
    catch (Exception ex)
    {
        log.LogError(ex, "PRINT ERROR");

        Console.WriteLine();
        Console.WriteLine("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
        Console.WriteLine("PRINT ERROR");
        Console.WriteLine(ex.ToString());
        Console.WriteLine("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
        Console.WriteLine();

        return Results.Problem(
            detail: ex.Message,
            statusCode: 500,
            title: "Print failed"
        );
    }
});

app.Run();

public record PrintJob(
    string Printer,

    [property: JsonPropertyName("job_id")]
    string JobId,

    int Copies,
    string Data
);