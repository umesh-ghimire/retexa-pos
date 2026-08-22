using System.Runtime.InteropServices;
using System.Text;
namespace RetexaPrintBridge;
public static class RawPrinter
{
    [StructLayout(LayoutKind.Sequential, CharSet=CharSet.Unicode)] private struct DOCINFO { public string pDocName; public string? pOutputFile; public string pDataType; }
    [DllImport("winspool.drv", SetLastError=true, CharSet=CharSet.Unicode)] static extern bool OpenPrinter(string pName,out IntPtr hPrinter,IntPtr defaults);
    [DllImport("winspool.drv",SetLastError=true)] static extern bool ClosePrinter(IntPtr hPrinter);
    [DllImport("winspool.drv",SetLastError=true,CharSet=CharSet.Unicode)] static extern bool StartDocPrinter(IntPtr hPrinter,int level,ref DOCINFO di);
    [DllImport("winspool.drv",SetLastError=true)] static extern bool EndDocPrinter(IntPtr hPrinter);
    [DllImport("winspool.drv",SetLastError=true)] static extern bool StartPagePrinter(IntPtr hPrinter);
    [DllImport("winspool.drv",SetLastError=true)] static extern bool EndPagePrinter(IntPtr hPrinter);
    [DllImport("winspool.drv",SetLastError=true)] static extern bool WritePrinter(IntPtr hPrinter,byte[] data,int count,out int written);
    public static void Send(string printerName, byte[] data)
    {
        if(!OperatingSystem.IsWindows()) throw new PlatformNotSupportedException("RAW printing requires Windows.");
        if(!OpenPrinter(printerName,out var h,IntPtr.Zero)) throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error());
        try { var di=new DOCINFO{pDocName="RETEXA POS Receipt",pDataType="RAW"}; if(!StartDocPrinter(h,1,ref di)) throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error()); try { if(!StartPagePrinter(h)) throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error()); try { if(!WritePrinter(h,data,data.Length,out var written)||written!=data.Length) throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error()); } finally { EndPagePrinter(h); } } finally { EndDocPrinter(h); } } finally { ClosePrinter(h); }
    }
}
