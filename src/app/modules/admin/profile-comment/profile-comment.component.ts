import {
  Component,
  EventEmitter,
  Input,
  OnInit,
  Output,
  ViewChild,
} from "@angular/core";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import * as moment from "moment";
import { NgForm } from "@angular/forms";
import { StaffService } from "app/services/staff.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { GlobalVariable } from "app/shared/global";
import { JobRoster1Service } from "app/services/job-roster1.service";

@Component({
  selector: "app-profile-comment",
  templateUrl: "./profile-comment.component.html",
  styleUrls: ["./profile-comment.component.scss"],
})
export class ProfileCommentComponent implements OnInit {

  conversation: string[] = ["Hello", "Hi", "Where are you?", "I am at the store"];

  @ViewChild("inputField") inputField: NgForm;
  @Output() feedbackArrayLength = new EventEmitter<number>();
  @Input() guardId;
  @Input() type;
  @Input() jobroster;
  @Input() rosterType;
  feedback;
  formattedDate: any;
  username: any;
  selectUpdate: boolean = false;
  feedback_id: any;
  uniforms: any
  constructor(
    private modalService: NgbActiveModal,
    private adminStaff: StaffService,
    private toast: ToastServiceService,
    private globals: GlobalVariable,
    private jobRoster: JobRoster1Service,
  ) { }
  now = moment().format("MMMM Do YYYY, h:mm a");

  inputValue: string;
  inputEdit: string = null;
  buttonColor: boolean = false;
  iconColor: string = "black";
  ngOnInit(): void {
    
    this.getfeedBack();
    this.username = JSON.parse(localStorage.getItem("admin"));
  }

  close(data) {
    this.modalService.dismiss(data);
  }
  onShiftEnter(event: KeyboardEvent): void {
    if (event.shiftKey) {
      event.preventDefault();
      const textarea = event.target as HTMLTextAreaElement;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const value = textarea.value;
      textarea.value = value.substring(0, start) + '\n' + value.substring(end);
      textarea.selectionStart = textarea.selectionEnd = start + 1;
      this.inputValue = textarea.value;
    }
  }
  submitForm() {
    if (this.selectUpdate == false) {
      if(this.jobroster == 'note'){
        if(this.noteId){
          let data = {
            admin_id: this.globals.admin.admin_id,
            jobroster_id: this.globals.roster_id,
            reason: this.inputValue,
            id: this.noteId
          }
          this.jobRoster.adminNote(data).subscribe((res) => {
            if (res.success) {
              this.getfeedBack();
              this.inputValue = null;
              let status = "Admin Notes";
              this.toast.toastNotification(res.message, status);
            }
            else {
              let status = "Admin Notes";
              this.toast.toastNotification(res.message, status);
            }
          })
        }
        else{
          let data = {
            admin_id: this.globals.admin.admin_id,
            jobroster_id: this.globals.roster_id,
            reason: this.inputValue
          }
        
          this.jobRoster.adminNote(data).subscribe((res) => {
            if (res.success) {
              this.getfeedBack();
              this.inputValue = null;
              let status = "Admin Notes";
              this.toast.toastNotification(res.message, status);
            }
            else {
              let status = "Admin Notes";
              this.toast.toastNotification(res.message, status);
            }
          })
        }
      }

      else if(this.rosterType === 'runsheet_roster'){
        if(this.noteId){
          let data = {
            admin_id: this.globals.admin.admin_id,
            jobroster_id: this.globals.roster_id,
            reason: this.inputValue,
            id: this.noteId
          }
          console.log("Update Runsheet admin notes", data)
        }
        else{
          let data = {
            admin_id: this.globals.admin.admin_id,
            jobroster_id: this.globals.roster_id,
            reason: this.inputValue
          }
          console.log("Submit Runsheet admin notes", data)
        }
      }

      else{
        console.log(this.guardId);
        let data = {
          guard_id: this.guardId,
          admin_id: this.username.admin_id,
          feedback: this.inputValue,
        };
        this.adminStaff.adminFeedback(data).subscribe((res) => {
          if (res.success) {
            this.getfeedBack()
            this.inputValue = null;
            this.buttonColor = false;
            let status = "admin feedback";
            this.toast.toastNotification(res.message, status);
          }
        });

      }

    }

    if (this.selectUpdate == true) {
      let data = {
        guard_id: this.guardId,
        admin_id: this.username.admin_id,
        feedback: this.inputValue,
        admin_type: this.username.admin_user_type,
        id: this.feedback_id
      };
      this.adminStaff.update_Feedback(data).subscribe((res) => {
        if (res.success) {
          this.getfeedBack()

          this.inputValue = null;
          this.buttonColor = false;
          this.selectUpdate = false;
          let status = "admin feedback";
          this.toast.toastNotification(res.message, status);
        }
      });
    }
  }

  updateFeedback(text) {
    this.feedback_id = text.id
    this.selectUpdate = !this.selectUpdate
    console.log(this.selectUpdate);
    if (this.selectUpdate == true) {
      this.inputValue = text.feedback;
    }
  }

  activity;
  getfeedBack() {
    if (this.type == 'uniform') {
      this.adminStaff.getUniformHistory(this.guardId).subscribe(({ success, data }) => {
        if (success) {
          this.uniforms = data.map(element => {
            // Check if 'after_update' is not "null" before parsing
            if (element.after_update !== "null") {
              element.uniform_type = JSON.parse(JSON.parse(element.after_update).uniform_type);
            } else {
              element.uniform_type = null; // Or handle it as needed
            }
            return element;
          });
          console.log(this.uniforms);
          
        }
      });
    }
    else if(this.jobroster == 'note'){
      let data = {
        roster_id: this.globals.roster_id,
        admin_id: this.globals.admin.admin_id
      }
      this.jobRoster.getadminactivity(data).subscribe(({ success, data }) => {
        if (success) {
          this.activity = data;
        }
      })

    }

    else if(this.rosterType == 'runsheet_roster'){
      let data = {
        roster_id: this.globals.roster_id,
        admin_id: this.globals.admin.admin_id
      }
      console.log("Get admin activity", data)
    }
    else {
      this.adminStaff.getFeedback(this.guardId).subscribe((res) => {
        if (res.success) {
          res.data.forEach(element => {
            const createdDate = moment.unix(element.created_at);
            element.date = createdDate.format("MMMM D, YYYY h:mm");
            if (element.updated_at !== null && element.updated_at !== "") {
              const updatedDate = moment.unix(element.updated_at);
              element.date = updatedDate.format("MMMM D, YYYY h:mm");
            }
          });
          this.feedback = res.data;
        }
      });
    }
  }


  deleteFeedback(text) {
    this.feedback_id = text.id
    let data = {
      guard_id: this.guardId,
      admin_id: this.username.admin_id,
      id: this.feedback_id
    };
    this.adminStaff.delete(data).subscribe((res) => {
      if (res.success) {
        this.getfeedBack();
        this.inputValue = null;
        this.buttonColor = false;
        let status = "admin feedback";
        this.toast.toastNotification(res.message, status);
      }
    });
  }

  formatActionType(actionType: string): string {
    return actionType.replace(/_/g, ' ');
  }

  // noteId;
  deleteadminNote(note){
    if(this.rosterType === 'runsheet_roster'){
      console.log("Delete Runsheet vAdmin Feedback")
    }
    else{
      this.jobRoster.deletenote(note.id).subscribe((res) => {
        if (res.success) {
          this.getfeedBack();
          this.inputValue = null;
          this.buttonColor = false;
          let status = "admin note";
          this.toast.toastNotification(res.data, status);
        }
      });
    }
  }

  noteId;
  editadmin(text){
    this.noteId = text.id
    console.log(this.selectUpdate);
    this.inputValue = text.reason;
  }
}
function else_if(arg0: boolean) {
  throw new Error("Function not implemented.");
}

