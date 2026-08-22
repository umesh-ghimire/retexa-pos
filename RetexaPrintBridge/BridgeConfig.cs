namespace RetexaPrintBridge;
public sealed class BridgeConfig
{
    public string Host { get; set; } = "127.0.0.1";
    public int Port { get; set; } = 9123;
    public string[] AllowedPrinters { get; set; } = ["YICHIP POS58"];
    public int MaxBytes { get; set; } = 1024 * 1024;
}
