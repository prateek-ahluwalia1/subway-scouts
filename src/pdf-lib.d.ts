declare module 'pdf-lib' {
  export class PDFDocument {
    static load(data: ArrayBuffer | Uint8Array): Promise<PDFDocument>;
    getForm(): PDFForm;
    save(): Promise<Uint8Array>;
    getPages(): PDFPage[];
  }

  export class PDFForm {
    getFields(): PDFField[];
    getTextField(name: string): PDFTextField;
    createTextField(name: string): PDFTextField;
    createCheckBox(name: string): PDFCheckBox;
    createRadioGroup(name: string): PDFRadioGroup;
    removeField(field: PDFField): void;
  }

  export class PDFField {
    getName(): string;
    getWidgets(): PDFWidget[];
  }

  export class PDFTextField extends PDFField {
    setText(text: string): void;
    addToPage(page: PDFPage, options: { x: number; y: number; width: number; height: number }): void;
  }

  export class PDFCheckBox extends PDFField {
    addToPage(page: PDFPage, options: { x: number; y: number; width: number; height: number }): void;
  }

  export class PDFRadioGroup extends PDFField {
    addOptionToPage(name: string, page: PDFPage, options: { x: number; y: number; width: number; height: number }): void;
  }

  export class PDFPage {
    getHeight(): number;
    drawText(text: string, options: { x: number; y: number; size?: number; font?: any; color?: any }): void;
  }

  export class PDFWidget {
    getRectangle(): { x: number; y: number; width: number; height: number };
  }
}
