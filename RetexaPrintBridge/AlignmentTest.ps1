$printer = "YICHIP POS58"

$ESC = [char]27
$GS  = [char]29

$data = ""

# Initialize printer
$data += $ESC + "@"

# LEFT
$data += $ESC + "a" + [char]0
$data += "LEFT"
$data += "`n"

# CENTER
$data += $ESC + "a" + [char]1
$data += "CENTER"
$data += "`n"

# RIGHT
$data += $ESC + "a" + [char]2
$data += "RIGHT"
$data += "`n"

# Reset
$data += $ESC + "a" + [char]0

# Feed
$data += $ESC + "d" + [char]3

# Cut
$data += $GS + "V" + [char]0

$bytes = [System.Text.Encoding]::ASCII.GetBytes($data)

Add-Type -TypeDefinition @"
using System;
using System.Runtime.InteropServices;

public class AlignmentRawPrinter
{
    [StructLayout(LayoutKind.Sequential, CharSet=CharSet.Unicode)]
    public class DOCINFO
    {
        [MarshalAs(UnmanagedType.LPWStr)]
        public string pDocName;

        [MarshalAs(UnmanagedType.LPWStr)]
        public string pOutputFile;

        [MarshalAs(UnmanagedType.LPWStr)]
        public string pDataType;
    }

    [DllImport("winspool.drv", EntryPoint="OpenPrinterW")]
    public static extern bool OpenPrinter(
        string pPrinterName,
        out IntPtr phPrinter,
        IntPtr pDefault
    );

    [DllImport("winspool.drv")]
    public static extern bool ClosePrinter(IntPtr hPrinter);

    [DllImport("winspool.drv", EntryPoint="StartDocPrinterW")]
    public static extern int StartDocPrinter(
        IntPtr hPrinter,
        int level,
        DOCINFO di
    );

    [DllImport("winspool.drv")]
    public static extern bool EndDocPrinter(IntPtr hPrinter);

    [DllImport("winspool.drv")]
    public static extern int StartPagePrinter(IntPtr hPrinter);

    [DllImport("winspool.drv")]
    public static extern bool EndPagePrinter(IntPtr hPrinter);

    [DllImport("winspool.drv")]
    public static extern bool WritePrinter(
        IntPtr hPrinter,
        IntPtr pBytes,
        int dwCount,
        out int dwWritten
    );

    public static void Send(string printer, byte[] data)
    {
        IntPtr hPrinter;

        if (!OpenPrinter(printer, out hPrinter, IntPtr.Zero))
            throw new Exception(
                "Could not open printer: " + printer
            );

        try
        {
            DOCINFO di = new DOCINFO();

            di.pDocName = "ESC/POS ALIGNMENT TEST";
            di.pDataType = "RAW";

            if (StartDocPrinter(hPrinter, 1, di) == 0)
                throw new Exception("StartDocPrinter failed.");

            if (StartPagePrinter(hPrinter) == 0)
                throw new Exception("StartPagePrinter failed.");

            IntPtr unmanagedBytes =
                Marshal.AllocCoTaskMem(data.Length);

            try
            {
                Marshal.Copy(
                    data,
                    0,
                    unmanagedBytes,
                    data.Length
                );

                int written;

                if (!WritePrinter(
                    hPrinter,
                    unmanagedBytes,
                    data.Length,
                    out written))
                {
                    throw new Exception(
                        "WritePrinter failed."
                    );
                }
            }
            finally
            {
                Marshal.FreeCoTaskMem(unmanagedBytes);
            }

            EndPagePrinter(hPrinter);
            EndDocPrinter(hPrinter);
        }
        finally
        {
            ClosePrinter(hPrinter);
        }
    }
}
"@

[AlignmentRawPrinter]::Send(
    $printer,
    $bytes
)

Write-Host ""
Write-Host "============================================"
Write-Host "ESC/POS ALIGNMENT TEST SENT"
Write-Host "============================================"
Write-Host "Printer: $printer"