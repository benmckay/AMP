<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'request_type' => $this->request_type,
            'priority' => $this->priority,
            
            // Requester info
            'requester' => [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
                'email' => $this->requester->email,
            ],
            'requester_department' => $this->when($this->requesterDepartment, [
                'id' => $this->requesterDepartment?->id,
                'name' => $this->requesterDepartment?->name,
                'code' => $this->requesterDepartment?->code,
            ]),
            
            // Template info
            'template' => [
                'id' => $this->template->id,
                'mnemonic' => $this->template->mnemonic,
                'name' => $this->template->name,
                'display_name' => $this->template->display_name,
                'ehr_access_level' => $this->template->ehr_access_level,
            ],
            
            // Department info
            'department' => $this->when($this->department, [
                'id' => $this->department?->id,
                'name' => $this->department?->name,
                'code' => $this->department?->code,
            ]),
            
            // System info
            'system' => [
                'id' => $this->system->id,
                'name' => $this->system->name,
                'code' => $this->system->code,
            ],
            
            // Target user info
            'target_user' => [
                'payroll_number' => $this->payroll_number,
                'full_name' => $this->full_name,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'username' => $this->username,
                'job_title' => $this->job_title,
            ],
            
            // COS fields (if applicable)
            'cos_fields' => $this->when($this->provider_group, [
                'provider_group' => $this->provider_group,
                'provider_type' => $this->provider_type,
                'specialty' => $this->specialty,
                'service' => $this->service,
                'admitting' => $this->admitting,
                'ordering_physician' => $this->ordering_physician,
                'sign_orders' => $this->sign_orders,
                'cosign_orders' => $this->cosign_orders,
            ]),
            
            // Request metadata
            'justification' => $this->justification,
            
            // Workflow info
            'submitted_at' => $this->submitted_at,
            'approved_at' => $this->approved_at,
            'approved_by' => $this->when($this->approvedBy, [
                'id' => $this->approvedBy?->id,
                'name' => $this->approvedBy?->name,
            ]),
            'approval_comments' => $this->approval_comments,
            
            'fulfilled_at' => $this->fulfilled_at,
            'fulfilled_by' => $this->when($this->fulfilledBy, [
                'id' => $this->fulfilledBy?->id,
                'name' => $this->fulfilledBy?->name,
            ]),
            'fulfillment_notes' => $this->fulfillment_notes,
            
            'cancelled_at' => $this->cancelled_at,
            'cancelled_by' => $this->when($this->cancelledBy, [
                'id' => $this->cancelledBy?->id,
                'name' => $this->cancelledBy?->name,
            ]),
            'cancellation_reason' => $this->cancellation_reason,
            
            // Processing time
            'processing_time_days' => $this->processing_time,
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}