<?php

$dirs = [
    __DIR__ . '/storage/app/public/medical_student_docs/photos',
    __DIR__ . '/storage/app/public/medical_student_docs/ids',
    __DIR__ . '/storage/app/public/medical_student_docs/enrollments',
    __DIR__ . '/storage/app/public/medical_student_docs/special',
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Minimal 1-page valid PDF header & body
$samplePdfContent = "%PDF-1.4
1 0 obj <</Type /Catalog /Pages 2 0 R>> endobj
2 0 obj <</Type /Pages /Kids [3 0 R] /Count 1>> endobj
3 0 obj <</Type /Page /Parent 2 0 R /Resources <</Font <</F1 4 0 R>>>> /MediaBox [0 0 612 792] /Contents 5 0 R>> endobj
4 0 obj <</Type /Font /Subtype /Type1 /BaseFont /Helvetica>> endobj
5 0 obj <</Length 120>> stream
BT
/F1 18 Tf
50 700 Td
(Medical Student Document Sample - Verified Certificate) Tj
50 660 Td
(Document Type: Official University Enrollment Certificate) Tj
ET
endstream endobj
xref
0 6
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000246 00000 n 
0000000315 00000 n 
trailer <</Size 6 /Root 1 0 R>>
startxref
487
%%EOF";

// Create sample PDFs & images
$files = [
    '/storage/app/public/medical_student_docs/photos/demo1.jpg',
    '/storage/app/public/medical_student_docs/photos/demo2.jpg',
    '/storage/app/public/medical_student_docs/photos/demo3.jpg',
    '/storage/app/public/medical_student_docs/photos/demo4.jpg',
    '/storage/app/public/medical_student_docs/photos/demo5.jpg',

    '/storage/app/public/medical_student_docs/ids/demo1.pdf',
    '/storage/app/public/medical_student_docs/ids/demo2.pdf',
    '/storage/app/public/medical_student_docs/ids/demo3.pdf',
    '/storage/app/public/medical_student_docs/ids/demo4.pdf',
    '/storage/app/public/medical_student_docs/ids/demo5.pdf',

    '/storage/app/public/medical_student_docs/enrollments/demo1.pdf',
    '/storage/app/public/medical_student_docs/enrollments/demo2.pdf',
    '/storage/app/public/medical_student_docs/enrollments/demo3.pdf',
    '/storage/app/public/medical_student_docs/enrollments/demo4.pdf',
    '/storage/app/public/medical_student_docs/enrollments/demo5.pdf',

    '/storage/app/public/medical_student_docs/special/death1.pdf',
    '/storage/app/public/medical_student_docs/special/death4.pdf',
    '/storage/app/public/medical_student_docs/special/medical2.pdf',
    '/storage/app/public/medical_student_docs/special/sibling1.pdf',
    '/storage/app/public/medical_student_docs/special/sibling3.pdf',
];

// Sample SVG/JPEG data for photo
$samplePhoto = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"><rect width="300" height="300" fill="#2563eb"/><circle cx="150" cy="110" r="50" fill="#ffffff"/><path d="M60,260 C60,190 240,190 240,260" fill="#ffffff"/><text x="150" y="280" font-family="sans-serif" font-size="16" fill="#ffffff" text-anchor="middle">Student Photo</text></svg>');

foreach ($files as $file) {
    $fullPath = __DIR__ . $file;
    if (str_ends_with($file, '.pdf')) {
        file_put_contents($fullPath, $samplePdfContent);
    } else {
        // Create 1x1 dummy image or SVG photo
        file_put_contents($fullPath, file_get_contents($samplePhoto));
    }
}

echo "Sample files generated successfully!\n";
