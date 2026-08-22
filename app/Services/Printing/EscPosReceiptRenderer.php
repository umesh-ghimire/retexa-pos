<?php

namespace App\Services\Printing;

use App\Models\Sale;
use App\Models\BillTemplate;
use App\Services\Printing\Escpos\EscPosCommandBuilder;
use App\Services\Printing\Escpos\ColumnFormatter;
use Illuminate\Support\Facades\Log;

final class EscPosReceiptRenderer
{
    public function __construct(
        private ?TextEncoder $encoder = null
    ) {
        $this->encoder ??= new TextEncoder();
    }

    public function render(
        Sale $sale,
        BillTemplate $template,
        PrinterProfile $profile
    ): EscPosRenderResult {
        $b = new EscPosCommandBuilder();

        /*
         * ESC/POS uses character columns, not CSS pixels or mm.
         * The printer profile determines the actual printable width.
         */
        $c = new ColumnFormatter(
            $profile->printableColumns
        );

        $warnings = [];

        /*
         * ---------------------------------------------------------
         * INITIALIZE PRINTER
         * ---------------------------------------------------------
         */

        $b->init()->lineSpacing();

        /*
         * The Bill Designer alignment is used ONLY for
         * free-form text such as:
         *
         * - Shop name
         * - Address
         * - Phone
         * - VAT/PAN
         * - Header text
         * - Footer text
         *
         * Fixed-width rows are handled separately.
         */
        $alignment = $this->resolveAlignment(
            $template->alignment
        );

        Log::info('ESC/POS ALIGNMENT', [
            'template_alignment' => $template->alignment,
            'resolved_alignment' => $alignment,
            'printable_columns' => $profile->printableColumns,
        ]);

        /*
         * ---------------------------------------------------------
         * SECTION ORDER
         * ---------------------------------------------------------
         */

        $sections = $template->getSectionOrderOrDefault();

        /*
         * Some old templates may not contain barcode.
         */
        if (
            $template->show_barcode &&
            !in_array('barcode', $sections, true)
        ) {
            $footerIndex = array_search(
                'footer',
                $sections,
                true
            );

            if ($footerIndex === false) {
                $sections[] = 'barcode';
            } else {
                array_splice(
                    $sections,
                    $footerIndex,
                    0,
                    ['barcode']
                );
            }
        }

        /*
         * ---------------------------------------------------------
         * BUILD RECEIPT
         * ---------------------------------------------------------
         */

        foreach ($sections as $section) {

            switch ($section) {

                /*
                 * =================================================
                 * HEADER
                 * =================================================
                 */

                case 'header':

                    /*
                     * Header follows Bill Designer alignment.
                     */
                    $b->align($alignment);

                    /*
                     * Logo
                     *
                     * Native ESC/POS image printing is not currently
                     * implemented in this renderer.
                     */
                    if ($template->show_logo) {

                        $warnings[] =
                            'Logo is enabled in the template but native ESC/POS logo rendering is not configured.';
                    }

                    /*
                     * Shop name
                     */
                    if ($template->shop_name) {

                        $b
                            ->align($alignment)
                            ->bold(true)
                            ->size('double')
                            ->text(
                                $this->e(
                                    (string) $template->shop_name,
                                    $profile
                                )
                            )
                            ->newline()
                            ->size('normal')
                            ->bold(false);
                    }

                    /*
                     * Address
                     */
                    if ($template->address) {

                        $b
                            ->align($alignment)
                            ->text(
                                $this->e(
                                    (string) $template->address,
                                    $profile
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Phone
                     */
                    if ($template->phone) {

                        $b
                            ->align($alignment)
                            ->text(
                                $this->e(
                                    'Phone: ' . $template->phone,
                                    $profile
                                )
                            )
                            ->newline();
                    }

                    /*
                     * VAT / PAN
                     */
                    if ($template->vat_pan_number) {

                        $b
                            ->align($alignment)
                            ->text(
                                $this->e(
                                    'VAT/PAN: ' .
                                    $template->vat_pan_number,
                                    $profile
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Header text
                     */
                    if ($template->header_text) {

                        $b
                            ->align($alignment)
                            ->text(
                                $this->e(
                                    (string) $template->header_text,
                                    $profile
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Divider.
                     *
                     * It is already exactly printable width, so
                     * alignment does not matter.
                     */
                    $b
                        ->align('left')
                        ->text($c->separator())
                        ->newline();

                    break;


                /*
                 * =================================================
                 * BILL INFORMATION
                 * =================================================
                 */

                case 'bill_info':

                    /*
                     * IMPORTANT:
                     *
                     * These are fixed-width rows.
                     *
                     * Do NOT use the Bill Designer alignment here.
                     *
                     * The spaces generated by twoColumn() determine
                     * the position of the value.
                     */

                    $b->align('left');

                    /*
                     * Bill number
                     */
                    if ($template->show_bill_number) {

                        $b
                            ->text(
                                $c->twoColumn(
                                    'Bill No',
                                    (string) (
                                        $sale->bill_number ?? ''
                                    )
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Date / Time
                     */
                    if ($template->show_date) {

                        $createdAt =
                            optional($sale->created_at);

                        $date =
                            $createdAt->format('Y-m-d');

                        if (!$date) {
                            $date = date('Y-m-d');
                        }

                        $time =
                            $createdAt->format('H:i');

                        $b
                            ->text(
                                $c->twoColumn(
                                    'Date',
                                    $date
                                )
                            )
                            ->newline();

                        if ($time) {

                            $b
                                ->text(
                                    $c->twoColumn(
                                        'Time',
                                        $time
                                    )
                                )
                                ->newline();
                        }
                    }

                    /*
                     * Cashier
                     */
                    if (
                        $template->show_cashier &&
                        $sale->createdBy
                    ) {

                        $b
                            ->text(
                                $c->twoColumn(
                                    'Cashier',
                                    (string)
                                        $sale->createdBy->name
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Payment method
                     */
                    if (
                        $template->show_payment_method &&
                        $sale->payment_method
                    ) {

                        $b
                            ->text(
                                $c->twoColumn(
                                    'Payment',
                                    strtoupper(
                                        (string)
                                            $sale->payment_method
                                    )
                                )
                            )
                            ->newline();
                    }

                    break;


                /*
                 * =================================================
                 * CUSTOMER
                 * =================================================
                 */

                case 'customer_info':

                    if ($template->show_customer) {

                        $customerName = null;

                        if ($sale->customer) {

                            $customerName =
                                $sale->customer->name ?? null;
                        }

                        if (!$customerName) {

                            $customerName =
                                $sale->customer_name ?? null;
                        }

                        if ($customerName) {

                            /*
                             * Fixed-width row.
                             */
                            $b
                                ->align('left')
                                ->text(
                                    $c->twoColumn(
                                        'Customer',
                                        (string) $customerName
                                    )
                                )
                                ->newline();
                        }
                    }

                    break;


                /*
                 * =================================================
                 * ITEMS
                 * =================================================
                 */

                case 'items':

                    /*
                     * Full-width divider.
                     */
                    $b
                        ->align('left')
                        ->text($c->separator())
                        ->newline();

                    /*
                     * Items are already formatted into fixed columns.
                     */
                    foreach ($sale->items as $item) {

                        $itemName =
                            $item->item_name
                            ?? $item->name
                            ?? '';

                        /*
                         * SKU
                         */
                        if (
                            $template->show_sku &&
                            $item->product &&
                            $item->product->sku
                        ) {

                            $itemName .=
                                ' (' .
                                $item->product->sku .
                                ')';
                        }

                        /*
                         * Quantity
                         */
                        $qty =
                            $template->show_quantity
                            ? $this->formatNumber(
                                $item->quantity
                            )
                            : '';

                        /*
                         * Unit price
                         */
                        $price =
                            $template->show_price
                            ? $this->formatMoney(
                                $item->unit_price
                            )
                            : '';

                        /*
                         * ColumnFormatter creates the complete
                         * fixed-width item row.
                         */
                        $lines =
                            $c->itemRow(
                                $this->e(
                                    (string) $itemName,
                                    $profile
                                ),
                                $qty,
                                $price
                            );

                        foreach ($lines as $line) {

                            /*
                             * IMPORTANT:
                             *
                             * Do NOT apply center/right alignment.
                             *
                             * This line already contains all required
                             * spaces for the columns.
                             */
                            $b
                                ->align('left')
                                ->text($line)
                                ->newline();
                        }
                    }

                    break;


                /*
                 * =================================================
                 * TOTALS
                 * =================================================
                 */

                case 'totals':

                    /*
                     * Fixed-width totals.
                     */
                    $b
                        ->align('left')
                        ->text($c->separator())
                        ->newline();

                    /*
                     * Subtotal
                     */
                    if ($template->show_subtotal) {

                        $b
                            ->text(
                                $c->twoColumn(
                                    'Subtotal',
                                    $this->formatMoney(
                                        $sale->subtotal
                                    )
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Discount
                     */
                    if ($template->show_discount) {

                        $b
                            ->text(
                                $c->twoColumn(
                                    'Discount',
                                    $this->formatMoney(
                                        $sale->discount
                                    )
                                )
                            )
                            ->newline();
                    }

                    /*
                     * VAT
                     */
                    if ($template->calculate_vat) {

                        $vatPercent =
                            (float) (
                                $template->vat_percentage ?? 0
                            );

                        $vatAmount = 0;

                        if ($vatPercent > 0) {

                            $vatAmount =
                                (
                                    (float) $sale->total
                                    * $vatPercent
                                )
                                /
                                (100 + $vatPercent);
                        }

                        $vatLabel =
                            'VAT (' .
                            $this->formatNumber(
                                $vatPercent
                            ) .
                            '%)';

                        $b
                            ->text(
                                $c->twoColumn(
                                    $vatLabel,
                                    $this->formatMoney(
                                        $vatAmount
                                    )
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Grand total
                     */
                    $b
                        ->bold(true)
                        ->text(
                            $c->twoColumn(
                                'TOTAL',
                                $this->formatMoney(
                                    $sale->total
                                )
                            )
                        )
                        ->newline()
                        ->bold(false);

                    break;


                /*
                 * =================================================
                 * PAYMENT
                 * =================================================
                 */

                case 'payment':

                    /*
                     * Payment is fixed-width.
                     */
                    $b->align('left');

                    /*
                     * Payment method
                     */
                    if (
                        $template->show_payment_method &&
                        $sale->payment_method
                    ) {

                        $b
                            ->text(
                                $c->twoColumn(
                                    'Payment Method',
                                    strtoupper(
                                        (string)
                                            $sale->payment_method
                                    )
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Cash received
                     */
                    if ($template->show_cash_received) {

                        $b
                            ->text(
                                $c->twoColumn(
                                    'Cash',
                                    $this->formatMoney(
                                        $sale->cash_received
                                    )
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Change
                     */
                    if ($template->show_change) {

                        $b
                            ->text(
                                $c->twoColumn(
                                    'Change',
                                    $this->formatMoney(
                                        $sale->change_amount
                                    )
                                )
                            )
                            ->newline();
                    }

                    /*
                     * Due
                     */
                    if (
                        isset($sale->due_amount) &&
                        (float) $sale->due_amount > 0
                    ) {

                        $b
                            ->bold(true)
                            ->text(
                                $c->twoColumn(
                                    'Due',
                                    $this->formatMoney(
                                        $sale->due_amount
                                    )
                                )
                            )
                            ->newline()
                            ->bold(false);
                    }

                    break;


                /*
                 * =================================================
                 * BARCODE
                 * =================================================
                 */

                case 'barcode':

                    if ($template->show_barcode) {

                        if (!$profile->supportsBarcode) {

                            $warnings[] =
                                'Barcode requested but printer profile does not support native barcode commands.';

                        } else {

                            $warnings[] =
                                'Barcode is enabled but native ESC/POS barcode rendering is not implemented in this renderer.';
                        }
                    }

                    break;


                /*
                 * =================================================
                 * QR
                 * =================================================
                 */

                case 'qr':

                    if ($template->show_qr) {

                        if (!$profile->supportsQr) {

                            $warnings[] =
                                'QR requested but printer profile does not support native ESC/POS QR commands.';

                        } else {

                            $warnings[] =
                                'QR is enabled but native ESC/POS QR rendering is not implemented in this renderer.';
                        }
                    }

                    break;


                /*
                 * =================================================
                 * FOOTER
                 * =================================================
                 */

                case 'footer':

                    if ($template->footer_text) {

                        /*
                         * Footer follows Bill Designer alignment.
                         */
                        $b
                            ->align($alignment)
                            ->text(
                                $this->e(
                                    (string)
                                        $template->footer_text,
                                    $profile
                                )
                            )
                            ->newline();
                    }

                    break;
            }
        }

        /*
         * ---------------------------------------------------------
         * FINAL PRINTER STATE
         * ---------------------------------------------------------
         *
         * Restore left alignment before feed/cut.
         *
         * This prevents the printer from carrying a center/right
         * state into the next physical print operation.
         */

        $b->align('left');

        /*
         * Bottom feed.
         */
        $b->feed(3);

        /*
         * Cut paper if supported.
         */
        if ($profile->supportsCut) {
            $b->cut();
        }

        /*
         * ---------------------------------------------------------
         * RETURN RESULT
         * ---------------------------------------------------------
         */

        return new EscPosRenderResult(
            $b->toBytes(),
            $profile,
            $warnings
        );
    }


    /*
     * =============================================================
     * ALIGNMENT
     * =============================================================
     */

    private function resolveAlignment(
        ?string $alignment
    ): string {

        return match (
            strtolower(
                trim(
                    (string) $alignment
                )
            )
        ) {
            'center' => 'center',
            'right'  => 'right',
            default  => 'left',
        };
    }


    /*
     * =============================================================
     * MONEY
     * =============================================================
     */

    private function formatMoney($amount): string
    {
        return number_format(
            (float) $amount,
            2,
            '.',
            ''
        );
    }


    /*
     * =============================================================
     * NUMBER
     * =============================================================
     */

    private function formatNumber($number): string
    {
        $number = (float) $number;

        /*
         * Integer:
         * 1.000 -> 1
         * 5.000 -> 5
         */
        if (floor($number) == $number) {

            return (string) (int) $number;
        }

        /*
         * Decimal:
         * 1.500 -> 1.5
         * 1.250 -> 1.25
         */
        return rtrim(
            rtrim(
                number_format(
                    $number,
                    3,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }


    /*
     * =============================================================
     * TEXT ENCODING
     * =============================================================
     */

    private function e(
        string $text,
        PrinterProfile $profile
    ): string {

        /*
         * Current ESC/POS text renderer does not support
         * native Devanagari text.
         */
        if ($this->encoder->hasDevanagari($text)) {

            return '[Unicode text requires raster printing]';
        }

        return $this->encoder->encode(
            $text,
            $profile->encoding
        );
    }
}