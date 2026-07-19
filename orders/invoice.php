<?php
include('../config/db.php');
require('../fpdf/fpdf.php');

// Custom PDF class with header and footer
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo Image
        $logo_path = '../assets/logo.jpeg';
        if(file_exists($logo_path)) {
            $this->Image($logo_path, 15, 10, 40);
            $this->SetXY(60, 15);
        } else {
            $this->SetXY(15, 15);
        }

        // Company Name
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(68, 189, 50);
        $this->Cell(0, 10, 'Postajency.com', 0, 1, 'L');

        // Tagline
        $this->SetX(60);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Professional SEO & Digital Marketing Solutions', 0, 1, 'L');

        // Header Line
        $this->SetY(35);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(15, 40, 195, 40);

        // Invoice Title
        $this->SetY(50);
        $this->SetFont('Arial', 'B', 28);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(0, 15, 'INVOICE', 0, 1, 'R');

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, '____________________', 0, 1, 'R');
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-30);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->SetY(-25);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 5, 'Postajency.com - Professional SEO Solutions', 0, 1, 'C');
        $this->Cell(0, 5, 'Email: khubaib@postajency.com | Phone: +92 329 6175673', 0, 1, 'C');
        $this->Cell(0, 5, 'Thank you for your business!', 0, 1, 'C');
    }
}

$id = $_GET['id'];

// Fetch order details with client info
$order = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT orders.*, users.name as client_name, users.email as client_email 
FROM orders 
JOIN users ON orders.client_id = users.id
WHERE orders.id=$id
"));

// Fetch link insertions if any
$link_insertions = mysqli_query($conn, "SELECT * FROM link_insertions WHERE order_id=$id");

// Generate Invoice Number
$invoice_number = 'INV-' . date('Ymd') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
$invoice_date = date('F d, Y');
$due_date = date('F d, Y', strtotime('+7 days'));

$pdf = new PDF();
$pdf->AddPage();

// BILL TO SECTION
$pdf->SetY(70);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(0, 8, 'Bill To:', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 6, htmlspecialchars($order['client_name']), 0, 1);
$pdf->Cell(0, 6, htmlspecialchars($order['client_email']), 0, 1);

// INVOICE DETAILS BOX
$pdf->SetY(70);
$pdf->SetX(130);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(25, 8, 'Invoice #:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(40, 8, $invoice_number, 0, 1, 'L');

$pdf->SetX(130);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 8, 'Invoice Date:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 8, $invoice_date, 0, 1, 'L');

$pdf->SetX(130);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 8, 'Due Date:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 8, $due_date, 0, 1, 'L');

$pdf->SetX(130);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 8, 'Order ID:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 8, '#' . htmlspecialchars($order['order_id']), 0, 1, 'L');

// TABLE HEADER
$pdf->SetY(115);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(68, 189, 50);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Cell(120, 12, 'Description', 1, 0, 'L', true);
$pdf->Cell(40, 12, 'Amount', 1, 0, 'C', true);
$pdf->Cell(20, 12, 'Total', 1, 1, 'C', true);

// TABLE CONTENT
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFillColor(245, 245, 245);
$fill = false;

// Service Description with detailed link info
$description = htmlspecialchars($order['service_type']) . " Service\n\n";

// Add link insertion details if any
if(mysqli_num_rows($link_insertions) > 0) {
    $linkCount = 1;
    while($link = mysqli_fetch_assoc($link_insertions)) {
        $description .= "Link #" . $linkCount . ":\n";
        $description .= "- Existing post URL: " . htmlspecialchars($link['source_url']) . "\n";
        $description .= "- Anchor text: " . htmlspecialchars($link['anchor_text']) . "\n";
        $description .= "- Link to: " . htmlspecialchars($link['target_url']) . "\n\n";
        $linkCount++;
    }
}

// Draw description cell
$yBefore = $pdf->GetY();
$pdf->MultiCell(120, 5, trim($description), 1, 'L');
$descHeightUsed = $pdf->GetY() - $yBefore;

// Draw Amount and Total cells
$pdf->SetY($yBefore);
$pdf->SetX(135);
$pdf->Cell(40, $descHeightUsed, '$' . number_format($order['price'], 2), 1, 0, 'C');
$pdf->Cell(20, $descHeightUsed, '$' . number_format($order['price'], 2), 1, 1, 'C');

// SUBTOTAL
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(160, 10, 'Subtotal:', 1, 0, 'R', false);
$pdf->Cell(20, 10, '$' . number_format($order['price'], 2), 1, 1, 'C', false);

// TAX (0% for digital services example)
$pdf->Cell(160, 10, 'Tax (0%):', 1, 0, 'R', false);
$pdf->Cell(20, 10, '$0.00', 1, 1, 'C', false);

// TOTAL
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(68, 189, 50);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(160, 12, 'TOTAL DUE:', 1, 0, 'R', true);
$pdf->Cell(20, 12, '$' . number_format($order['price'], 2), 1, 1, 'C', true);

// ORDER DETAILS SECTION
$pdf->SetY($pdf->GetY() + 10);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(0, 8, 'Order Details:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);

$pdf->Cell(0, 6, 'Platform: ' . htmlspecialchars($order['platform']), 0, 1);
$pdf->Cell(0, 6, 'Status: ' . htmlspecialchars($order['status']), 0, 1);
$pdf->Cell(0, 6, 'Order Date: ' . date('F d, Y', strtotime($order['order_date'])), 0, 1);
$pdf->Cell(0, 6, 'Deadline: ' . date('F d, Y', strtotime($order['deadline'])), 0, 1);

if($order['completion_date']){
    $pdf->Cell(0, 6, 'Completion Date: ' . date('F d, Y', strtotime($order['completion_date'])), 0, 1);
}

// TERMS & CONDITIONS - FIXED: Using proper line breaks with MultiCell
$pdf->SetY($pdf->GetY() + 10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(0, 6, 'Terms & Conditions:', 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 100, 100);

// Create terms text as an array of lines
$terms_lines = array(
    '1. Payment is due within 7 days of invoice date.',
    '2. For any disputes, please contact us within 3 days of receiving this invoice.',
    '3. Services will be delivered as per the agreed timeline.',
    '4. This invoice is generated electronically and is valid without signature.'
);

foreach($terms_lines as $line) {
    $pdf->Cell(0, 5, $line, 0, 1);
}

// Contact Information
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(0, 6, 'Contact Information:', 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 100, 100);

// Create contact lines as an array
$contact_lines = array(
    'For any questions or concerns, please contact us at:',
    'Email: khubaib@postajency.com',
    'Phone: +92 329 6175673'
);

foreach($contact_lines as $line) {
    $pdf->Cell(0, 5, $line, 0, 1);
}

// Thank You Note
$pdf->SetY(-45);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(68, 189, 50);
$pdf->Cell(0, 6, 'Thank you for choosing Postajency.com!', 0, 1, 'C');

$pdf->Output('I', 'Invoice_' . $invoice_number . '.pdf');
?>