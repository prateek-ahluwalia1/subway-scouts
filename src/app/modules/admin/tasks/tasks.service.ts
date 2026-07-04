import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { BehaviorSubject, catchError, filter, map, Observable, of, switchMap, take, tap, throwError, timeout } from 'rxjs';
import { Tag, Task } from 'app/modules/admin/tasks/tasks.types';
import { GlobalVariable } from 'app/shared/global';

@Injectable({
    providedIn: 'root'
})
export class TasksService
{
    private _tags: BehaviorSubject<Tag[] | null> = new BehaviorSubject(null);
    private _task: BehaviorSubject<Task | null> = new BehaviorSubject(null);
    private _tasks: BehaviorSubject<Task[] | null> = new BehaviorSubject(null);

    httpOptions = {
        headers: new HttpHeaders({
          'Content-Type': 'application/json',
          'Access-Control-Allow-Origin': '*',
          'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
        }),
      };
    constructor(private _httpClient: HttpClient, private global: GlobalVariable)
    {
    }
    getTasks(): Observable<any> {
        let data = {
            admin_id : this.global.admin.admin_id
        }
        return this._httpClient.post<any>(`${this.global.baseUrl}get-all-operation-notes`,data, this.httpOptions).pipe(
            catchError(this.handleError)
        )
      }

    /**
     * Update tasks orders
     *
     * @param tasks
     */
    updateTasksOrders(tasks: Task[]): Observable<Task[]>
    {
        return this._httpClient.patch<Task[]>('api/apps/tasks/order', {tasks});
    }

    /**
     * Search tasks with given query
     *
     * @param query
     */
    searchTasks(query: string): Observable<Task[] | null>
    {
        return this._httpClient.get<Task[] | null>('api/apps/tasks/search', {params: {query}});
    }

    /**
     * Get task by id
     */
    getTaskById(id: string): Observable<Task>
    {
        return this._tasks.pipe(
            take(1),
            map((tasks) => {

                // Find the task
                const task = tasks.find(item => item.id === id) || null;

                // Update the task
                this._task.next(task);

                // Return the task
                return task;
            }),
            switchMap((task) => {

                if ( !task )
                {
                    return throwError('Could not found task with id of ' + id + '!');
                }

                return of(task);
            })
        );
    }

    /**
     * Create task
     *
     * @param type
     */
    createTask(type: string): Observable<any> {
        return this._httpClient.post<any>('https://your-api-domain.com/api/tasks', { type }).pipe(
            map((newTask) => {
                // Update the tasks with the new task
                const currentTasks = this._tasks.value || [];
                this._tasks.next([newTask, ...currentTasks]);
                // Return the new task
                return newTask;
            })
        );
    }

    saveNotes(data){
        data.admin_id = this.global.admin.admin_id
        return this._httpClient.post<any>(`${this.global.baseUrl}operation-notes-store`,data, this.httpOptions).pipe(
            catchError(this.handleError)
        )
    }

    viewNotes(id){
        let data = {
            id:id,
            admin_id:this.global.admin.admin_id
        }
        return this._httpClient.post<any>(`${this.global.baseUrl}operation-note-show`,data, this.httpOptions).pipe(
            catchError(this.handleError)
        )
    }

    readTask(task:any){
    
        return this._httpClient.post<any>(`${this.global.baseUrl}operation-notes-mark-as-read`,task, this.httpOptions).pipe(
            catchError(this.handleError)
        )
    }
 
    private handleError(error:any): Observable<any>{
        console.error('An error occurred:', error);
        return throwError('Something Went wrong. Please try again later.')
    }
}
