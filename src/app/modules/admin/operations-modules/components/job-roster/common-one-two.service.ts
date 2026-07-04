import { Injectable } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { FormServiceOneTwoService } from 'app/services/form-service-one-two.service';
import { SmsServicesService } from 'app/services/sms-services.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { Observable, switchMap, of } from 'rxjs';
import { ShiftDelReasonComponent } from './shift-del-reason/shift-del-reason.component';

@Injectable({
  providedIn: 'root'
})
export class CommonOneTwoService {
  addShiftBasicForm: FormGroup;
  dataToExchange: { reason: string } = {
    reason: '',
  };
  constructor(private global: GlobalVariable,
    public smsService: SmsServicesService, private formField: FormServiceOneTwoService, private toast: ToastServiceService) {
    this.addShiftBasicForm = this.formField.initializeAddShiftBasicForm()
  }
  getFormattedName(job: any): string {
    let name = '';
    if (job.first_name) {
      name = job.first_name + ' ' + job.last_name;
    } else if (job.unprofile_name && !job.guard_id) {
      return job.training ? job.unprofile_name + ' (T)' : job.unprofile_name;
    } else {
      return 'Unassigned Shift';
    }
    if (job.status === 'completed') {
      name += job.training ? ' (C)(T)' : ' (C)';
    } else if (job.status === 'missed') {
      name += job.training ? ' (M)(T)' : ' (M)';
    } else if (job.status === 'pending' && job.first_name) {
      name += job.training ? ' (T)' : '';
    } else if (job.status === 'confirmed' && job.signin_status === 1) {
      name += job.training ? ' (S)(T)' : ' (S)';
    } else if (job.status === 'confirmed' && job.signin_status === 0) {
      name += job.training ? ' (T)' : '';
    }

    return name || 'Unassigned Shift';
  }


  paste(day, id, rosterId, calenderType, copyJob) {
    const { start, end } = this.global.AddTimeDate(copyJob.start_time, copyJob.end_time, day?.momentFormat);
    const data: any = {
      roster_id: rosterId,
      newStart: start,
      newEnd: end,
      type: 'copy_shift',
      admin_id: this.global.admin.admin_id,
      guard_id: copyJob.guard_id
    };
    if (calenderType === 'staff') {
      data.guard_id = id;
    } else {
      data.site_ids = id;
    }
    return data
  }

  Runsheetpaste(day, id, rosterId, calenderType, copyJob) {
    const { start, end } = this.global.AddTimeDate(copyJob.start_time, copyJob.end_time, day?.momentFormat);
    const data: any = {
      roster_id: rosterId,
      newStart: start,
      newEnd: end,
      type: 'copy_shift',
      admin_id: this.global.admin.admin_id,
      guard_id: copyJob.guard_id
    };
    if (calenderType === 'guard') {
      data.guard_id = id;
    } else {
      data.site_ids = id;
    }
    return data
  }


  sendAndConfirmMessage(msg: string, recipientIds: number[]): Observable<any> {
    const data = {
      body: msg,
      to: recipientIds,
      admin_id: this.global.admin.admin_id,
      phone: [],
    };

    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure you want to confirm this action?');
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.SUCCESS,
      animationIn: AppearanceAnimation.SWING,
      animationOut: DisappearanceAnimation.BOUNCE_OUT,
      allowHtmlMessage: true,
      buttonPosition: 'right',
    });
    newConfirmBox.setButtonLabels('Confirm', 'Decline');

    return newConfirmBox.openConfirmBox$().pipe(
      switchMap((resp) => {
        if (resp.success) {
          return this.smsService.smsSend(data);
        } else {
          return of(resp);
        }
      })
    );
  }

  checkWeek(lockWeek: boolean) {
    const currentDate = moment(this.global.start);
    const startOfCurrentWeek = moment().startOf('isoWeek');
    if (this.global.admin.admin_user_type === 'super-admin') {
      return true;
    }

    if (currentDate.isSameOrAfter(startOfCurrentWeek)) {
      return true;
    }
    const today = moment();
    const currentDay = today.day();
    const currentTime = currentDate.format('HH:mm');
    const startOfPreviousTwoWeeks = moment().subtract(2, 'weeks').startOf('isoWeek');
    const endOfPreviousTwoWeeks = moment().subtract(1, 'weeks').endOf('isoWeek');
    if (lockWeek && currentDate.isBetween(startOfPreviousTwoWeeks, endOfPreviousTwoWeeks, undefined, '[]')) {
      return true
    }
    if (!lockWeek && currentDate.isBetween(startOfPreviousTwoWeeks, endOfPreviousTwoWeeks, undefined, '[]') && (currentDay === 1 || (currentDay === 2 && currentTime <= '10:00'))) {
      return true
    }
    return false;
  }




  shiftDelReason(id) {
    const dialogPopup = new DialogInitializer(ShiftDelReasonComponent);
    dialogPopup.setCustomData(this.dataToExchange);
    dialogPopup.setConfig({
      width: '500px',
      layoutType: DialogLayoutDisplay.DANGER // SUCCESS | INFO | NONE | DANGER | WARNING
    });

    dialogPopup.setCustomData({ reason: 'N/A', id: id });
    dialogPopup.setButtons([
      new ButtonMaker('Cancel', 'cancel', ButtonLayoutDisplay.LIGHT),
      new ButtonMaker('Submit', 'submit', ButtonLayoutDisplay.SUCCESS)
    ]);

    return dialogPopup.openDialog$();
  }

  RunsheetshiftDelReason(id) {
    const dialogPopup = new DialogInitializer(ShiftDelReasonComponent);
    dialogPopup.setCustomData(this.dataToExchange);
    dialogPopup.setConfig({
      width: '500px',
      layoutType: DialogLayoutDisplay.DANGER // SUCCESS | INFO | NONE | DANGER | WARNING
    });

    dialogPopup.setCustomData({ reason: '', id: id, type: 'run_sheet' });
    dialogPopup.setButtons([
      new ButtonMaker('Cancel', 'cancel', ButtonLayoutDisplay.LIGHT),
      new ButtonMaker('Submit', 'submit', ButtonLayoutDisplay.SUCCESS)
    ]);

    return dialogPopup.openDialog$();
  }

  checkShiftLengthAccess(isAdmin, max14Hours, shiftLength) {
    if (!isAdmin && !max14Hours) {
      if (parseFloat(shiftLength) > 12.00) {
        this.toast.toastNotification1('You don\'t have permission to create more than an 12-hour shift', 'Shift Error!');
        return false;
      }
      return true
    } else if (!isAdmin && max14Hours) {
      if (parseFloat(shiftLength) > 14.00) {
        this.toast.toastNotification1('You don\'t have permission to create more than an 14-hour shift', 'Shift Error!');
        return false;
      }
      return true;
    }
    else {
      return true
    }
  }

  shiftLength(start, end) {

    if (start && end) {
      const startTime = moment(start, 'HH:mm');
      const endTime = moment(end, 'HH:mm');
      // Check if end time is smaller than start time (crossing midnight)
      if (endTime.isBefore(startTime)) {
        endTime.add(1, 'day'); // Add 1 day to end time
      }
      const durationMinutes = endTime.diff(startTime, 'minutes');
      const durationHours = Math.floor(durationMinutes / 60);
      const durationMinutesRemainder = durationMinutes % 60;
      return `${durationHours}.${durationMinutesRemainder < 10 ? '0' : ''}${durationMinutesRemainder}`;
    }
  }



}
