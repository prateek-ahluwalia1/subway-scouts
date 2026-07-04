import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
@Component({
  selector: 'app-chat-app',
  templateUrl: './chat-app.component.html',
  styleUrls: ['./chat-app.component.scss'],

})
export class ChatAppComponent implements OnInit {


  constructor(public router: Router,
  ) {


  }

  ngOnInit(): void {


  }
}
