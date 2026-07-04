import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { FullScreenService } from '../full-screen.service';
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { ServiceService } from 'app/services/service.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { MatDialog, MatDialogConfig, MatDialogRef } from '@angular/material/dialog';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

interface Task {
  id: number;
  title: string;
  description: string;
  user_id
}

@Component({
  selector: 'app-journal',
  templateUrl: './journal.component.html',
  styleUrls: ['../../new-dashboard/new-dashboard.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  styles: [`
    /*** FONTS ***/
    @import url(https://fonts.googleapis.com/css?family=Montserrat:900|Raleway:400,400i,700,700i);
    $black: #1d1f20;
    $blue: #83e4e2;
    $green: #a2ed56;
    $yellow: #fafafa;
    $white: #fafafa;

    %boxshadow {
      box-shadow: 0.25rem 0.25rem 0.6rem rgba(0,0,0,0.05), 0 0.5rem 1.125rem rgba(75,0,0,0.05);
    }

    main {
      display: block;
      margin: 0 auto;
      max-width: 40rem;
      padding: 1rem;
    }

    ol.gradient-list {
      counter-reset: gradient-counter;
      list-style: none;
      margin: 1.75rem 0;
      padding-left: 1rem;
      > li {
        background: white;
        border-radius: 0 0.5rem 0.5rem 0.5rem;
        @extend %boxshadow;
        counter-increment: gradient-counter;
        margin-top: 1rem;
        min-height: 3rem;
        padding: 1rem 1rem 1rem 3rem;
        position: relative;
        &::before,
        &::after {
          background: linear-gradient(135deg, $blue 0%,$green 100%);
          border-radius: 1rem 1rem 0 1rem;
          content: '';
          height: 3rem;
          left: -1rem;
          overflow: hidden;
          position: absolute;
          top: -1rem;
          width: 3rem;
        }
        &::before {
          align-items: flex-end;
          @extend %boxshadow;
          content: counter(gradient-counter);
          color: $black;
          display: flex;
          font: 900 1.5em/1 'Montserrat';
          justify-content: flex-end;
          padding: 0.125em 0.25em;
          z-index: 1;
        }
        @for $i from 1 through 5 {
          &:nth-child(10n+#{$i}):before {
            background: linear-gradient(135deg, rgba($green, $i * 0.2) 0%,rgba($yellow, $i * 0.2) 100%);
          }
        }
        @for $i from 6 through 10 {
          &:nth-child(10n+#{$i}):before {
            background: linear-gradient(135deg, rgba($green, 1 - (($i - 5) * 0.2)) 0%,rgba($yellow, 1 - (($i - 5) * 0.2)) 100%);
          }
        }
        + li {
          margin-top: 2rem;
        }
      }
    }
  `]
})

export class JournalComponent implements OnInit {

  @ViewChild('fullScreen') divRef;
  isFullscreen: boolean = false;
  showAddTaskSection: boolean = false;
  todoList: any = []
  newTask: Task = { id: null, title: '', description: '', user_id: localStorage.getItem('admin_id') };
  editingTask = false;
  unconverdShifts: any[] = []
  operationNotes: any[] = []
  operationalNotes:any[] = []
  @ViewChild('notes') notesDialogTemplate: TemplateRef<any>;
  @ViewChild('mockShifts') mockShifts: TemplateRef<any>;

  constructor(private fullScreenService: FullScreenService, private serivce: ServiceService,
    private toast: ToastServiceService, private _changeDetectorRef: ChangeDetectorRef, public globals: GlobalVariable,
    private dialog: MatDialog, private sanitizer: DomSanitizer) { }

  ngOnInit(): void {
    this.getAllToDos();
  }

  openFullscreen() {
    const elem = this.divRef.nativeElement;
    this.fullScreenService.toggleFullscreen(elem);
    this.isFullscreen = !this.isFullscreen;
  }

  toggleAddTaskSection() {
    this.editingTask = false
    this.showAddTaskSection = !this.showAddTaskSection;
    this.newTask = { id: null, title: '', description: '', user_id: localStorage.getItem('admin_id') };
  }
  
  getAllToDos() {
    const admin_id = this.globals.admin.admin_id
    const dialogConfig = new MatDialogConfig();
    dialogConfig.height = '100%';
    dialogConfig.width = '35%';
    dialogConfig.disableClose = true;
    dialogConfig.panelClass = 'custom-doc-detail';
    dialogConfig.position = {
      right: '0', // Adjust the value as needed to control the position from the right edge
      top: '0',   // Adjust the value as needed to control the position from the top edge
    };
    this.serivce.getAllToDos(admin_id).subscribe(({ success, data, mark, unconverd_shifts }) => {
      if (unconverd_shifts.length > this.unconverdShifts && unconverd_shifts.length > 0) {
        this.unconverdShifts = unconverd_shifts
        const acknowledged = JSON.parse(localStorage.getItem('acknowledged'));
        if (acknowledged) {
          const dialogRef: MatDialogRef<any> = this.dialog.open(this.mockShifts, dialogConfig);
        }
      }

      if (mark.length > 0 && this.globals.admin.userType!='saleperson') {
        // this.operationalNotes = mark
        const unreadMarks = mark.filter(item => item.is_read === false);
        this.operationNotes = unreadMarks;
        this.globals.unreadNotes = this.operationNotes.length
        if(this.operationNotes.length > 0){
          const dialogRef: MatDialogRef<any> = this.dialog.open(this.notesDialogTemplate, {
            minHeight: '80%',
            width: '100%',
            disableClose: true,
            panelClass: 'operations-notes-detail',
          });
          this._changeDetectorRef.markForCheck();
  
          dialogRef.afterClosed().subscribe(result => {
            console.log('Dialog closed with result:', result);
          });
        }
      }
      if (success) {
        this.todoList = data
      }
      this._changeDetectorRef.markForCheck()
    });
  }

  saveToDo() {
    if (this.editingTask) {
      this.serivce.updateToDo(this.newTask.id, this.newTask).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, 'Journal Operation!')
          this.newTask = { id: null, title: '', description: '', user_id: localStorage.getItem('admin_id') };
          this.toggleAddTaskSection();
          this.getAllToDos();
        }
      });
      this._changeDetectorRef.markForCheck()
    } else {

      if (!this.newTask.title.trim()) {
        this.toast.toastNotification1('Title is required', 'Invalid Form!')
        return;
      }

      if (!this.newTask.description.trim()) {
        this.toast.toastNotification1('Description is required', 'Invalid Form!')
        return;
      }

      this.serivce.addToDo(this.newTask).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, 'Journal Operation!')
          this.newTask = { id: null, title: '', description: '', user_id: localStorage.getItem('admin_id') };
          this.toggleAddTaskSection();
          this.getAllToDos();
        }
      });
      this._changeDetectorRef.markForCheck()

    }
  }

  addTask() {
    this.toggleAddTaskSection();
    this.editingTask = false
  }

  editTodo(item) {
    this.toggleAddTaskSection();
    this.editingTask = true;
    this.newTask = {
      id: item.id,
      title: item.title,
      description: item.description,
      user_id: localStorage.getItem('admin_id')
    };
  }

  changeTodoStatus(event) {
    this.serivce.changeTodoStatus(event.id).subscribe(({ status, msg }) => {
      if (status) {
        this.toast.toastNotification(msg, 'Journal Operation!')
        this.getAllToDos();
      }
    });

  }
  delTodo(id) {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Journal Operation!');
    newConfirmBox.setMessage('Are you sure to perform this action');
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.WARNING, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'right', // optional 
    });
    newConfirmBox.setButtonLabels('Confirm', 'Decline');
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        this.serivce.delToDo(id).subscribe(({ status, msg }) => {
          if (status) {
            this.toast.toastNotification1(msg, 'Journal Operation')
            this.getAllToDos()
          }
        });
      }

    });

  }

  readAll() {
    const ids = this.operationNotes.map(obj => obj.id)
    let data = {
      id: ids,
      admin_id: this.globals.admin.admin_id
    }
    this.serivce.markAsRead(data).subscribe(({ success, msg }) => {
      if (success) {
        this.toast.toastNotification(msg, 'Operation Notes!')
      }
    }, (() => {
      this.toast.toastNotification1('Something went wrong. Please try again later', 'Operation Notes!')
    }))
  }


  sanitizeHtml(html: string): SafeHtml {
    return this.sanitizer.bypassSecurityTrustHtml(html);
  }

  onCheckboxChange(item: any) {
    let data = {
      id: [item.id],
      admin_id: this.globals.admin.admin_id
    }
    this.serivce.markAsRead(data).subscribe(({ success, msg }) => {
      if (success) {
        this.getAllToDos()
        this.toast.toastNotification(msg, 'Operation Notes!')
      }
    })
  }

  acknowledged() {
    localStorage.setItem('acknowledged', JSON.stringify(false));
  }
}
