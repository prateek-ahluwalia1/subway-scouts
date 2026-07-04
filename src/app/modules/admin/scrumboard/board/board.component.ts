import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup } from '@angular/forms';
import { CdkDragDrop, moveItemInArray, transferArrayItem } from '@angular/cdk/drag-drop';
import { Subject, takeUntil } from 'rxjs';
import * as moment from 'moment';
import { FuseConfirmationService } from '@fuse/services/confirmation';
import { ScrumboardService } from '../scrumboard.service';
import { Board, Card, List } from '../scrumboard.models';
import { ActivatedRoute } from '@angular/router';
import { MatDialog } from '@angular/material/dialog';
import { ScrumboardCardDetailsComponent } from '../card/details/details.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastServiceService } from 'app/services/toast-service.service';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ServiceService } from 'app/services/service.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
    selector: 'scrumboard-board',
    templateUrl: './board.component.html',
    styleUrls: ['./board.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ScrumboardBoardComponent implements OnInit, OnDestroy {
    board: any = [];
    listTitleForm: UntypedFormGroup;

    showTooltip = false;
    // Private
    private readonly _positionStep: number = 65536;
    private readonly _maxListCount: number = 200;
    private readonly _maxPosition: number = this._positionStep * 500;
    private _unsubscribeAll: Subject<any> = new Subject<any>();

    /**
     * Constructor
     */
    constructor(
        private _changeDetectorRef: ChangeDetectorRef,
        private _formBuilder: UntypedFormBuilder,
        private _fuseConfirmationService: FuseConfirmationService,
        private _scrumboardService: ScrumboardService,
        private route: ActivatedRoute,
        private ngModel: NgbModal,
        public snackBar: MatSnackBar,
        private global: GlobalVariable,
    ) {
        this.global.showCrmTab = true

    }

    // -----------------------------------------------------------------------------------------------------
    // @ Lifecycle hooks
    // -----------------------------------------------------------------------------------------------------

    /**
     * On init
     */
    ngOnInit(): void {
        // Initialize the list title form
        this.listTitleForm = this._formBuilder.group({
            title: ['']
        });

        this.route.paramMap.subscribe(params => {
            const boardId = params.get('boardId');
            if (boardId) {
                this.getBoard(boardId)
            }
        });

    }

    getBoard(id) {
        // Get the board
        this._scrumboardService.getBoard(id).subscribe(res => {
            if (res) {
                this.board = res
                this._changeDetectorRef.detectChanges();
                this._changeDetectorRef.markForCheck();

            }
        })
    }

    /**
     * On destroy
     */
    ngOnDestroy(): void {
        this.global.showCrmTab = false
        // Unsubscribe from all subscriptions
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }

    // -----------------------------------------------------------------------------------------------------
    // @ Public methods
    // -----------------------------------------------------------------------------------------------------

    /**
     * Focus on the given element to start editing the list title
     *
     * @param listTitleInput
     */
    renameList(listTitleInput: HTMLElement): void {
        // Use timeout so it can wait for menu to close
        setTimeout(() => {
            listTitleInput.focus();
        });
    }

    /**
     * Add new list
     *
     * @param title
     */
    addList(title: string): void {
        // Limit the max list count
        if (this.board.lists.length >= this._maxListCount) {
            return;
        }

        // Create a new list model
        const list = new List({
            boardId: this.board.id,
            position: this.board.lists.length ? this.board.lists[this.board.lists.length - 1].position + this._positionStep : this._positionStep,
            title: title
        });

        // Save the list
        this._scrumboardService.createList(list).subscribe();
        this._changeDetectorRef.detectChanges();
        this._changeDetectorRef.markForCheck();
        this.route.paramMap.subscribe(params => {
            const boardId = params.get('boardId');
            if (boardId) {
                this.getBoard(boardId)
            }
        });
    }

    /**
     * Update the list title
     *
     * @param event
     * @param list
     */
    updateListTitle(event: any, list: List): void {
        // Get the target element
        const element: HTMLInputElement = event.target;

        // Get the new title
        const newTitle = element.value;

        // If the title is empty...
        if (!newTitle || newTitle.trim() === '') {
            // Reset to original title and return
            element.value = list.title;
            return;
        }

        // Update the list title and element value
        list.title = element.value = newTitle.trim();

        // Update the list
        this._scrumboardService.updateList(list).subscribe();
        this._changeDetectorRef.detectChanges();
        this._changeDetectorRef.markForCheck();
        this.route.paramMap.subscribe(params => {
            const boardId = params.get('boardId');
            if (boardId) {
                this.getBoard(boardId)
            }
        });
    }

    /**
     * Delete the list
     *
     * @param id
     */
    deleteList(id): void {
        // Open the confirmation dialog
        const confirmation = this._fuseConfirmationService.open({
            title: 'Delete list',
            message: 'Are you sure you want to delete this list and its cards? This action cannot be undone!',
            actions: {
                confirm: {
                    label: 'Delete'
                }
            }
        });

        // Subscribe to the confirmation dialog closed action
        confirmation.afterClosed().subscribe((result) => {

            // If the confirm button pressed...
            if (result === 'confirmed') {

                // Delete the list
                this._scrumboardService.deleteList(id).subscribe();
                this._changeDetectorRef.detectChanges();
                this._changeDetectorRef.markForCheck();
                this.route.paramMap.subscribe(params => {
                    const boardId = params.get('boardId');
                    if (boardId) {
                        this.getBoard(boardId)
                    }
                });
            }
        });
    }

    openSnackBar(message: string, action: string) {
        this.snackBar.open(message, action, {
            duration: 3000,
        });
    }
    /**
     * Add new card
     */
    addCard(list: List, customer_id: any): void {
        // Create a new card model
        const card = new Card({
            boardId: this.board.id,
            listId: list.id,
            position: list.cards.length ? list.cards[list.cards.length - 1].position + this._positionStep : this._positionStep,
            customer_id: customer_id.customer_id
        });
        // Save the card
        this._scrumboardService.createCard(card).subscribe(res => {
            if (res.success) {
                this.openSnackBar("The customer has been added successfully.", "Close");
                this._changeDetectorRef.detectChanges();
                this._changeDetectorRef.markForCheck();
                this.route.paramMap.subscribe(params => {
                    const boardId = params.get('boardId');
                    if (boardId) {
                        this.getBoard(boardId)
                    }
                });
            }
        });

    }

    /**
     * List dropped
     *
     * @param event
     */
    listDropped(event: CdkDragDrop<List[]>): void {
        console.log(event);

        // Move the item
        moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);

        // Calculate the positions
        const updated = this._calculatePositions(event);

        // Update the lists
        this._scrumboardService.updateLists(updated).subscribe();
    }

    /**
     * Card dropped
     *
     * @param event
     */
    cardDropped(event: CdkDragDrop<Card[]>): void {

        // Move or transfer the item
        if (event.previousContainer === event.container) {
            // Move the item
            moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        }
        else {
            // Transfer the item
            transferArrayItem(event.previousContainer.data, event.container.data, event.previousIndex, event.currentIndex);
            // Update the card's list it
            event.container.data[event.currentIndex].listId = event.container.id;
        }

        // Calculate the positions
        const updated = this._calculatePositions(event);

        // Update the cards
        this._scrumboardService.updateCards(updated).subscribe();
        this._changeDetectorRef.detectChanges();
        this._changeDetectorRef.markForCheck();
        this.route.paramMap.subscribe(params => {
            const boardId = params.get('boardId');
            if (boardId) {
                this.getBoard(boardId)
            }
        });
    }

    /**
     * Check if the given ISO_8601 date string is overdue
     *
     * @param date
     */
    isOverdue(date: string): boolean {
        return moment(date, moment.ISO_8601).isBefore(moment(), 'days');
    }

    /**
     * Track by function for ngFor loops
     *
     * @param index
     * @param item
     */
    trackByFn(index: number, item: any): any {
        return item.id || index;
    }

    // -----------------------------------------------------------------------------------------------------
    // @ Private methods
    // -----------------------------------------------------------------------------------------------------

    /**
     * Calculate and set item positions
     * from given CdkDragDrop event
     *
     * @param event
     * @private
     */
    private _calculatePositions(event: CdkDragDrop<any[]>): any[] {
        // Get the items
        let items = event.container.data;
        const currentItem = items[event.currentIndex];
        const prevItem = items[event.currentIndex - 1] || null;
        const nextItem = items[event.currentIndex + 1] || null;

        // If the item moved to the top...
        if (!prevItem) {
            // If the item moved to an empty container
            if (!nextItem) {
                currentItem.position = this._positionStep;
            }
            else {
                currentItem.position = nextItem.position / 2;
            }
        }
        // If the item moved to the bottom...
        else if (!nextItem) {
            currentItem.position = prevItem.position + this._positionStep;
        }
        // If the item moved in between other items...
        else {
            currentItem.position = (prevItem.position + nextItem.position) / 2;
        }

        // Check if all item positions need to be updated
        if (!Number.isInteger(currentItem.position) || currentItem.position >= this._maxPosition) {
            // Re-calculate all orders
            items = items.map((value, index) => {
                value.position = (index + 1) * this._positionStep;
                return value;
            });

            // Return items
            return items;
        }

        // Return currentItem
        return [currentItem];
    }

    openCardDetail(id) {
        const modalRef = this.ngModel.open(ScrumboardCardDetailsComponent, { windowClass: "add-new-stagging", scrollable: true })
        modalRef.componentInstance.id = id;
        modalRef.componentInstance.type = 'customer';
        modalRef.result.then((result) => {
            console.log("Modal Result", `Closed with: ${result}`)
        }, (reason) => {
            if (reason) {
                this._changeDetectorRef.detectChanges();
                this._changeDetectorRef.markForCheck();
                this.route.paramMap.subscribe(params => {
                    const boardId = params.get('boardId');
                    if (boardId) {
                        this.getBoard(boardId)
                    }
                });
            }

        });

    }

    getInitials(name: string): string {
        if (!name) {
            return ''; // Return an empty string if the name is null or undefined
        }

        const initials = name
            .split(' ')
            .map(word => word.charAt(0))
            .join('');
        return initials;
    }
}
