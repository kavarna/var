import {Component, ViewChild} from 'angular2/core';
import {Router} from 'angular2/router';

import {UserService} from 'variation-user/services';

import {Footertip} from '../footertip/footertip';

import {PortalSelect} from '../portal-select/portal-select';
import {PortalMain} from '../portal-main/portal-main';
import {Alerts} from '../alerts/alerts';

import {I18nService} from '../services/i18n/i18n';

@Component({
  selector: 'home-cmp',
  styleUrls: ['app/home/home.css'],
  templateUrl: './app/home/home.html',
  providers: [],
  directives: [PortalSelect, PortalMain, Alerts, Footertip]
})
export class HomeCmp {
  @ViewChild('portalmain') portalmain;

  // Here we define this component's instance variables
  // They're accessible from the template
  constructor(private router: Router, private userService: UserService, private i18n: I18nService) {
  }

  ngOnInit() {}

  getUser() { return this.userService.getLogin(); }

  logout() {
    this.userService.disconnect();
    this.router.parent.navigateByUrl('/login');
  }

  onPortalSelected(porId) { this.portalmain.setPortalId(porId); }
}
