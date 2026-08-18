<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class StudentExcelExporter
{
    public function download(Collection $students): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'students-');
        if ($path === false) {
            throw new \RuntimeException('تعذر إنشاء ملف التصدير المؤقت.');
        }

        $this->write($path, $students);
        $filename = 'students-'.now()->format('Ymd-His').'.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function write(string $path, Collection $students): void
    {
        $universities = ['IUG' => 'الجامعة الإسلامية بغزة', 'AUG' => 'جامعة الأزهر بغزة', 'ISRAA' => 'جامعة الإسراء', 'UPAL' => 'جامعة فلسطين'];
        $levels = ['level_1' => 'السنة الأولى', 'level_2' => 'السنة الثانية', 'level_3' => 'السنة الثالثة', 'level_4' => 'السنة الرابعة', 'level_5' => 'السنة الخامسة', 'level_6' => 'السنة السادسة', 'internship' => 'سنة الامتياز'];
        $housing = ['house' => 'منزل', 'tent' => 'خيمة', 'apartment' => 'شقة', 'relatives' => 'منزل أقارب', 'shelter' => 'مركز إيواء', 'other' => 'أخرى'];
        $rows = [[
            'الترتيب', 'الاسم', 'رقم الهوية', 'رقم الجوال', 'الجامعة', 'المستوى', 'المعدل', 'نوع السكن',
            'ابن شهيد/متوفى', 'إعاقة', 'أخ/أخت بالجامعة', 'اسم الأخ/الأخت', 'جامعة الأخ/الأخت', 'الرقم المرجعي',
        ]];

        foreach ($students as $index => $student) {
            $rows[] = [
                $index + 1, $student->full_name, $student->national_id, $student->mobile_number,
                $universities[$student->university_id] ?? $student->university_id,
                $levels[$student->academic_level] ?? $student->academic_level,
                (float) $student->gpa, $housing[$student->housing_type] ?? $student->housing_type,
                $student->is_father_martyr === 'yes' ? 'نعم' : 'لا',
                $student->has_disability === 'yes' ? 'نعم' : 'لا',
                $student->has_sibling_student === 'yes' ? 'نعم' : 'لا',
                $student->sibling_name, $student->sibling_university, $student->reference_number,
            ];
        }

        $sheetRows = '';
        foreach ($rows as $rowIndex => $row) {
            $number = $rowIndex + 1;
            $cells = '';
            foreach ($row as $columnIndex => $value) {
                $ref = $this->columnName($columnIndex + 1).$number;
                if ($rowIndex > 0 && in_array($columnIndex, [0, 6], true)) {
                    $cells .= '<c r="'.$ref.'" s="'.($columnIndex === 6 ? '2' : '1').'"><v>'.$this->xml($value).'</v></c>';
                } else {
                    $cells .= '<c r="'.$ref.'" t="inlineStr" s="'.($rowIndex === 0 ? '3' : '1').'"><is><t>'.$this->xml($value).'</t></is></c>';
                }
            }
            $sheetRows .= '<row r="'.$number.'"'.($rowIndex === 0 ? ' ht="30" customHeight="1"' : '').'>'.$cells.'</row>';
        }

        $lastRow = count($rows);
        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView rightToLeft="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols><col min="1" max="1" width="11" customWidth="1"/><col min="2" max="2" width="32" customWidth="1"/><col min="3" max="4" width="18" customWidth="1"/><col min="5" max="14" width="22" customWidth="1"/></cols>'
            .'<sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:N'.$lastRow.'"/></worksheet>';
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Arial"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF2563EB"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment horizontal="right" vertical="center" wrapText="1"/></xf><xf numFmtId="2" fontId="0" fillId="0" borderId="0" xfId="0"><alignment horizontal="center"/></xf><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf></cellXfs></styleSheet>';

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('تعذر إنشاء ملف Excel.');
        }
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="الطلاب" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->close();
    }

    private function xml(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }
}
