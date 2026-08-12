/* ============================================
   SHARED HELPER FUNCTIONS
   ============================================ */

/**
 * Format a number as Nepali Rupees currency text.
 * Example: formatCurrency(450) -> "Rs. 450"
 */
function formatCurrency(amount) {
    const safeAmount = isNaN(amount) ? 0 : parseFloat(amount);
    return "Rs. " + Math.round(safeAmount).toLocaleString("en-IN");
}

/**
 * Generate today's date in a readable format.
 * Example: "2026-08-10"
 */
function getTodayDateString() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0");
    const dd = String(today.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
}