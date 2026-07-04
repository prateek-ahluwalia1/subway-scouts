import { Component, OnInit, OnDestroy, ViewChild, ChangeDetectorRef } from '@angular/core';
import { FormBuilder } from '@angular/forms';
import { AgentService } from 'app/services/crm/agent.service';
import { GlobalVariable } from 'app/shared/global';
import { NgxSpinnerService } from 'ngx-spinner';
import { ToastServiceService } from 'app/services/toast-service.service';
import { DateAdapter } from '@angular/material/core';

import { NgxSelectDropdownComponent } from 'ngx-select-dropdown';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { ServiceService } from 'app/services/service.service';

@Component({
    selector: 'tasks-form',
    templateUrl: './tasks-form.component.html',
    styleUrls: ['./tasks-form.component.scss']

})
export class TasksFormComponent implements OnInit, OnDestroy {

    title
    @ViewChild('dropdown') dropdown: NgxSelectDropdownComponent;
    typeOptions = ["Lead", "Contact", "SEO", "Designing", "Content Creation", "Ads"];
    data: any = []
    statuses = [
        { label: 'Not Started', value: 'not_started' },
        { label: 'Deferred', value: 'deferred' },
        { label: 'In Progress', value: 'in_progress' },
        { label: 'Completed', value: 'completed' },
        { label: 'Waiting for Input', value: 'waiting_for_input' }
    ];

    priorities = [
        { label: 'Lowest', value: 'lowest' },
        { label: 'Low', value: 'low' },
        { label: 'Normal', value: 'normal' },
        { label: 'High', value: 'high' },
        { label: 'Highest', value: 'highest' }
    ];

    selectedStatus;
    selectedPriority;
    taskOwner: string = '';
    subject: string = '';
    description: string = '';
    dueDate: string;
    selectedType: any;
    selectedOption: any;
    agent_id: any;
    typeConfig = {
        search: false,
    };
    dropdownOptions = [];
    config = {
        search: true
    };
    selectedContact: any;
    private sub: Subscription;
    id

    salePerson: any[] = []
    constructor(
        private cdRef: ChangeDetectorRef,
        private route: ActivatedRoute, private adminService: ServiceService,
        private service: AgentService, private router: Router,
        private global: GlobalVariable, private spinnerService: NgxSpinnerService,
        private toast: ToastServiceService, public dateAdapter: DateAdapter<Date>,
    ) {
        this.dateAdapter.setLocale('en-AU');
        this.global.showCrmTab = true
    }

    ngOnInit(): void {
        this.taskOwner = this.global.admin.admin_name
        this.selectedType = 'Lead';


        this.sub = this.route.params.subscribe(
            params => {
                let id = +params['id'];
                if (id) {
                    this.getTask(id);
                    this.title = 'Update'
                }
                else {
                    this.getLeadBasedType()
                    this.title = 'Create'
                }
            }
        );

        this.getSalePerson()

    }

    getTask(id: number): void {
        this.service.editCrmTask(id)
            .subscribe(
                (task) => {
                    this.onTaskRetrieved(task.data);
                },
            );
    }
    onTaskRetrieved(task): void {
        this.selectedType = task?.selectedType
        this.getLeadBasedType()
        setTimeout(() => {
            if (task) {
                const selectedPriorityObject = this.priorities.find(item => item.value === task?.priority);
                const selectedStatusObject = this.statuses.find(item => item.value === task?.status);
                this.selectedType = task.selectedType

                if (selectedStatusObject) {
                    this.selectedStatus = selectedStatusObject.value;
                }
                if (selectedPriorityObject) {
                    this.selectedPriority = selectedPriorityObject;
                }

                this.global.selectedStaff = task?.agent_id;
                this.global.updateSelectedOption(this.global.selectedStaff);

                this.id = task.id
                this.description = task.description
                this.dueDate = task.due_date
                this.taskOwner = task.task_owner
                this.subject = task.subject

                // const dropdownOptions = this.data.find(item => item.id == task?.contact);
                // console.log(dropdownOptions);

                // if (dropdownOptions) {
                //     this.selectedContact = dropdownOptions
                //     this.selectedOption = dropdownOptions.name; // Assuming 'name' is the property you want to display
                // } else {
                //     this.selectedOption = null;
                // }
            }
        }, 1000);
    }

    ngOnDestroy(): void {
        this.global.showCrmTab = false
        this.sub.unsubscribe();
    }

    onDropdownChange(event: any) {
        console.log(event);
        this.selectedContact = this.data.find(item => item.name === event.value);
        if (this.selectedContact) {
            const selectedId = this.selectedContact.id;
        } else {
        }
        this.clearSearchInput();
    }


    clearSearchInput() {
        // Reset the search input by updating the ngModel binding
        this.dropdownOptions = [...this.dropdownOptions]; // Trigger change detection
    }

    onTypeChange(event: any, typeDropdown: any) {
        this.getLeadBasedType()
    }

    onSubmit() {
        console.log(this.selectedContact);
        const formData = {
            id: this.id ?? '',
            task_owner: this.taskOwner,
            due_date: this.dueDate, // Update this with the actual variable for due date
            contact: this.selectedContact?.id, // Update this with the appropriate variable for contact
            status: this.selectedStatus,
            priority: this.selectedPriority?.value,
            description: this.description,
            subject: this.subject,
            selectedType: this.selectedType,
            agent_id: this.agent_id,
            created_by:localStorage.getItem('admin_id'),

        };
        this.spinnerService.show()
        this.service.addUpdateCrmTask(formData).subscribe(({ msg, success }) => {
            if (success) {
                this.spinnerService.hide()
                this.router.navigate(['/crm/tasks']);
                this.toast.toastNotification(msg, 'Task Operation!');
            }
            else {
                this.spinnerService.hide()
                this.toast.toastNotification1('Something Went Wrong', 'Task Operation!')
            }

        })
    }

    getLeadBasedType() {
        let lowerCaseType = this.selectedType?.toLowerCase(); // Use the 'value' property
        if (lowerCaseType == 'contact') {
            lowerCaseType = 'contacted'
        }
        // this.service.getContactList(lowerCaseType)
        //     .subscribe(({ data, success }) => {
        //         if (data) {
        //             this.data = data
        //             this.dropdownOptions = data.map(item => item.name);
        //         }
        //         else {
        //             this.data = []
        //             this.dropdownOptions = [];
        //         }
        //     });
    }

    receiveDataFromChildSingle(data: any) {
        this.agent_id = data?.value.id
    }

    getSalePerson() {
        const data = {
            type: 'saleperson',
            user_type: 'salesperson'
        };

        this.adminService.getAdmin('active', data).subscribe(res => {
            if (res.success) {
                this.salePerson = res.data


            }
        }, (error) => {
            console.log(error);
        });
    }

}
