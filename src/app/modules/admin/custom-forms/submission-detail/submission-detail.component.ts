import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuildService } from '../form-builder.service';
import { trigger, transition, style, animate, query, stagger } from '@angular/animations';
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
import { TDocumentDefinitions } from "pdfmake/interfaces";
import { FormElementsService } from '../form-elements.service';
pdfMake.vfs = pdfFonts.pdfMake.vfs;
@Component({
  selector: 'app-submission-detail',
  templateUrl: './submission-detail.component.html',
  styleUrls: ['./submission-detail.component.scss'],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('300ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('300ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ]
})
export class SubmissionDetailComponent implements OnInit {

  previewData: any[] = []
  title: any
  formDataKeys: any[] = [];
  private excludedKeys = ['submit', 'heading', 'group'];
  formElements: any
  formBuilder: any
  formHeading
  logo

  constructor(private route: ActivatedRoute, private _service: FormBuildService, private router: Router,
    private _serviceForm: FormElementsService

  ) { }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      this.title = params['title'];
      const id = params['id'];
      if (id) {
        this._service.formSubmissionDetail(id).subscribe(res => {
          this.previewData = res.history;
          const formbuilder = res.details
          this.logo = formbuilder.logo
          this.formBuilder = JSON.parse(formbuilder?.body)
          this.formBuilder?.forEach(element => {
            if (element.type == 'heading') {
              this.formHeading = element.label
            }
          });
        });
      }
    });
  }


  getHeaders(): string[] {
    let headers = [];

    const addHeaders = (fields: any[]) => {
      if (Array.isArray(fields)) {
        for (let field of fields) {
          if (!this.excludedKeys.includes(field.type)) {
            headers.push(field.label);
          }
          if (field.type === 'group' && field.fields) {
            addHeaders(field.fields);
          }
        }
      }
    };

    if (this.formBuilder) {
      addHeaders(this.formBuilder);
    }

    return headers;
}




  getValues(item: any): any[] {
    

    
    let values = [];
    const addValues = (fields: any[], formData: any) => {
      if(formData){
        
      for (let field of formData) {
        if (field.type === 'single') {
          if (field.name === 'signature') {
            values.push({ type: 'signature', label: field.label, value: field.value });
          } else {
            values.push({ type: 'text', label: field.label, value: field.value });
          }
        } else if (field.type === 'dropdown') {
          values.push({ type: 'text', label: field.label, value: field.value });
        } else if (field.type === 'group') {
          for (let subField of field.fields) {
            values.push({ type: 'text', label: subField.label, value: subField.value });
          }
        }
      }
      }
    };
    addValues(this.formBuilder, item.form_data);
    return values;
}



  getFullName(formData: any): string {
    if (formData.FullName) {
      return `${formData.FullName.firstName} ${formData.FullName.lastName}`;
    }
    return '';
  }

  resolvePath(object: any, path: string, defaultValue: any = null): any {
    return path.split('.').reduce((o, p) => o && o[p] !== undefined ? o[p] : defaultValue, object);
  }

  async generatePDF(item) {
    const getBase64Image = (imgPath: string): Promise<string> => {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = "Anonymous";

            img.onload = () => {
                const canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0);

                const dataURL = canvas.toDataURL("image/png");
                resolve(dataURL);
            };

            img.onerror = () => {
                reject(new Error("Error loading image"));
            };

            img.src = imgPath;
        });
    };

    const logoDataURL = await getBase64Image("/assets/images/logo/scouts.png");

    const toTitleCase = (str) => {
      return str.replace(/\w\S*/g, (txt) => {
          return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
      });
  };

  const formatDate = (date) => {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `Date: ${day}-${month}-${year}`;
};

    const values = this.getValues(item);
    const details = values.map((data) => {
        if (data.type === 'signature') {
            return {
                key: { text: `${data.label}:`, style: "bulletHeadings" },
                value: { image: data.value, width: 100, height: 50, margin: [0, 5, 0, 10] }
            };
        } else {
            return {
                key: { text: `${data.label}`, style: "bulletHeadings" },
                value: { text: data.value || 'undefined', style: "bulletsValues", fillColor: '#eaecf9', margin: [0, 5, 0, 10] }
            };
        }
    });

    let docDefinition: TDocumentDefinitions = {
        pageSize: "A4",
        content: [
            {
                table: {
                    heights: [100, 25],
                    widths: ['40%', '40%', '20%'],
                    body: [
                        [
                            {
                                image: logoDataURL,
                                width: 100,
                                height: 100
                            },
                            {
                                stack: [
                                    { text: toTitleCase(this.formHeading ?? 'Default Title'), fontSize: 18, color: "black", bold: true, margin: [20, 30, 0, 20] },
                                ],
                            },
                            {
                                stack: [
                                    { text: formatDate(item.created_at), alignment: 'right', margin: [0, 20, 0, 0] },
                                ],
                            },
                        ]
                    ],
                },
                layout: {
                    defaultBorder: false,
                },
                fillColor: '#eaecf9',
            },
            {
                columns: [
                    {
                        width: "100%",
                        stack: [{ text: '', style: "subheader" }],
                    },
                ],
                margin: [0, 10, 0, 5],
            },
            {
                table: {
                    widths: ['20%', '80%'],
                    body: details.map(detail => {
                        return [
                            detail.key,
                            detail.value
                        ];
                    }),
                  dontBreakRows: true
                  
                },
                layout: {
                  paddingTop: function () {
                    return 8;
                  },
                  paddingBottom: function () {
                    return 8;
                  },
                  
                },
            }
        ],
        styles: {
            header1: {
                color: 'black',
                bold: true,
                margin: [40, 0, 0, 0]
            },
            headerText: {
                color: 'black',
                alignment: 'right',
                margin: [0, 0, 10, 0]
            },
            subheader: {
                fontSize: 15,
                bold: true,
                margin: [0, 0, 0, 20],
                color: "black",
            },
            bulletHeadings: {
                fontSize: 13,
                bold: true,
                margin: [0, 10, 0, 10]
            },
            bulletsValues: {
                fontSize: 12,
                margin: [0, 5, 0, 10],
                color: "#44454c",
                fillColor: '#eaecf9'
            },
            box: {
                fillColor: "#eaecf9",
                fontSize: 13
            }
        }
    };

    pdfMake.createPdf(docDefinition).download(this.title ?? 'Scout Form');
}



  async generateAllPDFs() {
    const getBase64Image = (imgPath: string): Promise<string> => {
      return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = "Anonymous";
  
        img.onload = () => {
          const canvas = document.createElement("canvas");
          canvas.width = img.width;
          canvas.height = img.height;
  
          const ctx = canvas.getContext("2d");
          ctx.drawImage(img, 0, 0);
  
          const dataURL = canvas.toDataURL("image/png");
          resolve(dataURL);
        };
  
        img.onerror = () => {
          reject(new Error("Error loading image"));
        };
  
        img.src = imgPath;
      });
    };
  
    const logoDataURL = await getBase64Image("/assets/images/logo/scouts.png");

    const toTitleCase = (str) => {
      return str.replace(/\w\S*/g, (txt) => {
          return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
      });
  };

  const formatDate = (date) => {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `Date: ${day}-${month}-${year}`;
};
    let content = [];
  
    for (const item of this.previewData) {
      if (!item.form_data) {
        // Skip items with null form_data
        continue;
      }
  
      const values = this.getValues(item);
      const details = values.map((data) => {
        if (data.type === 'signature') {
          return [
            { text: `${data.label}:`, style: "bulletHeadings" },
            { image: data.value, width: 100, height: 50, margin: [0, 5, 0, 10] }
          ];
        } else {
          return [
            { text: `${data.label}`, style: "bulletHeadings" },
            { text: data.value || 'undefined', style: "bulletsValues", fillColor: '#eaecf9', margin: [0, 5, 0, 10] }
          ];
        }
      });
  
      content.push(
        {
          table: {
            heights: [100, 25],
            widths: ['40%', '40%', '20%'],
            body: [
              [
                {
                  image: logoDataURL,
                  width: 100,
                  height: 100
                },
                {
                  stack: [
                    { text: toTitleCase(this.formHeading ?? 'Default Title'), fontSize: 18, color: "black", bold: true, margin: [20, 30, 0, 20] },
                  ],
                },
                {
                  stack: [
                    { text: formatDate(item.created_at), alignment: 'right', margin: [0, 20, 0, 0] },
                  ],
                },
              ]
            ],
          },
          layout: {
            defaultBorder: false,
          },
          fillColor: '#eaecf9',
        },
        {
          columns: [
            {
              width: "100%",
              stack: [{ text:  '', style: "subheader" }],
            },
          ],
          margin: [0, 10, 0, 5],
        },
        {
          table: {
            widths: ['20%', '80%'],
            body: details,
            dontBreakRows: true

          }
        },
        { text: '', pageBreak: 'after' }
      );
    }
  
    // Remove the last page break
    if (content.length > 0) {
      content[content.length - 1].pageBreak = undefined;
    }
  
    let docDefinition: TDocumentDefinitions = {
      pageSize: "A4",
      content: content,
      styles: {
        header1: {
          color: 'black',
          bold: true,
          margin: [40, 0, 0, 0]
        },
        headerText: {
          color: 'black',
          alignment: 'right',
          margin: [0, 0, 10, 0]
        },
        subheader: {
          fontSize: 15,
          bold: true,
          margin: [0, 0, 0, 20],
          color: "black",
        },
        bulletHeadings: {
          fontSize: 13,
          bold: true,
          margin: [0, 10, 0, 10]
        },
        bulletsValues: {
          fontSize: 12,
          margin: [0, 5, 0, 10],
          color: "#44454c",
          fillColor: '#eaecf9'
        },
        box: {
          fillColor: "#eaecf9",
          fontSize: 13
        }
      }
    };
  
    pdfMake.createPdf(docDefinition).download(this.title ?? 'Scout Form All.pdf');
  }
  

}
