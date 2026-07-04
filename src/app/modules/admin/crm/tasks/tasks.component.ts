import { GlobalVariable } from 'app/shared/global';
import { Component, OnInit, ViewChild } from '@angular/core';
import * as _ from 'lodash';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { AgentService } from 'app/services/crm/agent.service';
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { ToastServiceService } from 'app/services/toast-service.service';
import { PermissionsService } from 'app/services/permissions.service';
@Component({
  selector: 'app-tasks',
  templateUrl: './tasks.component.html',
  styleUrls: ['../crm-client/customer-list.component.css']
})
export class TasksComponent implements OnInit {

  @ViewChild(MatPaginator) paginator: MatPaginator;
  @ViewChild(MatSort) sort: MatSort;

  pageTitle: string = 'Tasks';
  imageWidth: number = 30;
  imageMargin: number = 2;
  showImage: boolean = false;
  listFilter: any = {};
  errorMessage: string;
  tasks: any = [];
  displayedColumns = ["due_date", "status", "priority","assigned_agent", "task_owner", "id"];
  dataSource: any = null;
  pager: any = {};
  pagedItems: any[];
  searchFilter: any = {
    firstname: "",
    lastname: "",
    email: ""
  };
  selectedOption: string;

  routeId
  leadType
  adminPermissions: any;
  constructor(private global: GlobalVariable,
    private service: AgentService,
    private toast: ToastServiceService, private permissionService: PermissionsService
  ) { }

  ngOnInit(): void {
    const per = this.permissionService.getPermissionsByTitle('CRM');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Tasks');
    this.global.showCrmTab = true
    this.getAllTaskS()
  }

  ngOnDestroy(): void {
    this.global.showCrmTab = false
  }

  resetListFilter() {
    this.listFilter = {};
  }

  reset() {
    this.listFilter = {};
    this.searchFilter = {};

  }

  resetSearchFilter(searchPanel: any) {
    searchPanel.toggle();
    this.searchFilter = {};
  }
  freshDataList(tasks) {
    this.tasks = tasks;
    this.dataSource = new MatTableDataSource(this.tasks.data);
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }
  applyFilter(filterValue: string) {
    filterValue = filterValue.trim(); // Remove whitespace
    filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
    this.dataSource.filter = filterValue;
  }

  getAllTaskS() {
    const user = JSON.parse(localStorage.getItem('admin'));
    const data = { 
      type: user.userType,
      admin_id: localStorage.getItem('admin_id')

   };
    this.service.getAllCrmTask(data)
      .subscribe(tasks => {
        this.freshDataList(tasks);
      },
        error => this.errorMessage = <any>error);

    this.searchFilter = {};
    this.listFilter = {};

  }

  openDialog(id) {
    this.openConfirmBox(id)
  }

  openConfirmBox(id) {
    const newConfirmBox = new ConfirmBoxInitializer();

    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure you want to confirm this action?');

    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.WARNING, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'right', // optional 
    });

    newConfirmBox.setButtonLabels('Confirm', 'Decline');

    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        this.service.deleteCrmTask(id).subscribe(({ msg, success }) => {
          if (success) {
            this.toast.toastNotification(msg, 'Task Operation!')
            this.getAllTaskS()
          }
          else {
            this.toast.toastNotification1('Something went wront', 'Task Operation!')
          }
        })
      }
    });
  }
}
