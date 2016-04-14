import {Component, Input, Output, EventEmitter, ViewChild, ElementRef, Renderer} from 'angular2/core';

import {BUTTON_DIRECTIVES} from 'ng2-bootstrap/ng2-bootstrap';

import {PgService} from '../services/pg-service/pg-service';
import {I18nService} from '../services/i18n/i18n';
import {AlertsService} from '../services/alerts/alerts';

@Component({
  selector: 'personsection-add',
  styleUrls: [],
  templateUrl: 'app/personsection-add/personsection-add.html',
  providers: [],
  directives: [BUTTON_DIRECTIVES],
})
export class PersonsectionAdd {
  @Input('porId') porId: number;
  @Input('entity') entity: string;
  @Output() onadded: EventEmitter<void> = new EventEmitter<void>();
  @ViewChild('inputname') inputname: ElementRef;

  sectionname: any;
  gettingName: boolean;

  constructor(
    private pgService: PgService, private i18n: I18nService, private alerts: AlertsService,
    private renderer: Renderer) {
    this.gettingName = false;
  }

  onAddSection() {
    this.gettingName = true;
    setTimeout(() => this.setFocusToInputName());
  }

  cancelAddSection() {
    this.gettingName = false;
    this.sectionname = '';
  }

  doAddSection() {
    this.pgService
      .pgcall(
      'portal', 'personsection_add',
      { prm_por_id: this.porId, prm_entity: this.entity, prm_name: this.sectionname })
      .then(newPseId => {
        this.onadded.emit(null);
        this.alerts.success(this.i18n.t('portal.alerts.personsection_added'));
      })
      .catch(err => {
        this.alerts.danger(this.i18n.t('portal.alerts.error_adding_personsection'));
      });
    this.cancelAddSection();
  }

  setFocusToInputName() {
    this.renderer.invokeElementMethod(this.inputname.nativeElement, 'focus', null);
  }
}
