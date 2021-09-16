<?php 

namespace UVDesk\CommunityPackages\UVDesk\FormComponent\Controller;

use Doctrine\ORM\Query;
use Doctrine\Common\Collections\Collection; 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse; 
use Webkul\UVDesk\CoreFrameworkBundle\Entity\TicketType; 
use Webkul\UVDesk\CoreFrameworkBundle\Entity\SupportRole; 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UVDesk\CommunityPackages\UVDesk\FormComponent\Entity\Form;
use Webkul\UVDesk\CoreFrameworkBundle\Services\UserService;
use UVDesk\CommunityPackages\UVDesk\FormComponent\Entity\CustomFields;  
use UVDesk\CommunityPackages\UVDesk\FormComponent\Entity\SavedCustomFields; 
use UVDesk\CommunityPackages\UVDesk\FormComponent\Entity\CustomFieldsValues;
use Webkul\UVDesk\CoreFrameworkBundle\Services\FileUploadService;
use Webkul\UVDesk\CoreFrameworkBundle\Services\CustomFieldsService;
use UVDesk\CommunityPackages\UVDesk\FormComponent\Entity\TicketCustomFieldsValues;
use UVDesk\CommunityPackages\UVDesk\FormComponent\Entity\UpdatedForm;
use UVDesk\CommunityPackages\UVDesk\FormComponent\Repository\FormBuilderRepository;

class FormBuilderController extends BaseController
{

    public function loadFormBuilders(Request $request)
    {
        $userRole = $this->get('user.service')->getCurrentUser()->getCurrentInstance()->getSupportRole()->getCode();

        if ($this->container->get('user.service')->isAccessAuthorized($userRole)) {
            return $this->render('@_uvdesk_extension_uvdesk_form_component\FormBuilders\formBuilderListConfigurations.html.twig', []);
        }

        return $this->render('@UVDeskCoreFramework//dashboard.html.twig', []);
        
    }

    public function createFormBuilderConfiguration(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $customFields =  $em->getRepository('UVDeskFormComponentPackage:CustomFields')->findAll();
        $customFieldsArray = [];

        foreach ($customFields as $k => $v) {
            $cfRepo = $this->getDoctrine()->getRepository(CustomFieldsValues::class); 
            $cfValues = $cfRepo->findOneBy([
                'customFields' => $v->getId(),
            ]);

            if ($v->getStatus() == true) {
                $temp = [
                    'id'        => $v->getId(),
                    'name'      => $v->getName(),
                    'fieldType' => $v->getFieldType(),
                    'value'     => $v->getValue(),
                    'required'  => $v->getRequired(),
                    'validation'=> json_decode($v->getValidation(), true),
                ]; 

                $customFieldsArray[$k] = $temp; 
            } 
        }

        if ($request->getMethod() == 'POST') {
            $form = new Form(); 
            $form->setFormName($request->request->get('form_name'));
            $setEmailValues['id'] = "102";
            $setEmailValues['fieldType'] = "email";
            $setEmailValues['name'] = $request->request->get("EmailName") ?? "Email";
            $setEmailValues['placeholder'] = $request->request->get("emailPlaceHolder") ?? "Customer email address";
            $setEmailValues['status'] = '1';
            $form->setEmail(serialize($setEmailValues));
            $setReplyValues['id'] = "105";
            $setReplyValues['fieldType'] = "reply";
            $setReplyValues['name'] = $request->request->get("replyName") ?? "Message";
            $setReplyValues['placeholder'] = $request->request->get("replyPlaceHolder") ?? "Ticket query message";
            $setReplyValues['status'] = '1';
            $form->setReply(serialize($setReplyValues));
            $setNameValues['id'] = "101";
            $setNameValues['fieldType'] = "name";
            $setNameValues['name'] = $request->request->get("nameName") ?? "Name";
            $setNameValues['placeholder'] = $request->request->get("namePlaceHolder") ?? "Customer full name";
            $setNameValues['status'] = $request->request->get('name') == '1' ? '1' : '0';
            $form->setName(serialize($setNameValues));
            $setTypeValues['id'] = '104';
            $setTypeValues['fieldType'] = 'type';
            $setTypeValues['name'] = $request->request->get("typeName") ?? "Type";
            $setTypeValues['placeholder'] = $request->request->get("typePlaceHolder") ?? "Choose ticket type";
            $setTypeValues['status'] = $request->request->get('type') == '1' ? '1' : '0';
            $form->setType(serialize($setTypeValues));
            $setSubjectValues['id'] = '103';
            $setSubjectValues['fieldType'] = 'subject';
            $setSubjectValues['name'] = $request->request->get("subjectName") ?? "Subject";
            $setSubjectValues['placeholder'] = $request->request->get("subjectPlaceHolder") ?? "Ticket subject";
            $setSubjectValues['status'] = $request->request->get('subject') == '1' ? '1' : '0';
            $form->setSubject(serialize($setSubjectValues));
            $setGdprValues['id'] = '108';
            $setGdprValues['fieldType'] = 'GDPR';
            $setGdprValues['name'] = $request->request->get("gdprName") ?? "Gdpr";
            $setGdprValues['placeholder'] = $request->request->get("gdprPlaceHolder") ?? "Gdpr";
            $setGdprValues['status'] = $request->request->get('gdpr') == '1' ? '1' : '0';
            $form->setGDPR(serialize($setGdprValues));
            $setOrderNoValues['id'] = '106';
            $setOrderNoValues['fieldType'] = 'order_no';
            $setOrderNoValues['name'] = $request->request->get("orderNoName") ?? "Order No";
            $setOrderNoValues['placeholder'] = $request->request->get("orderNoPlaceHolder") ?? "Order No";
            $setOrderNoValues['status'] = $request->request->get('order_no') == '1' ? '1' : '0';
            $form->setOrderNo(serialize($setOrderNoValues));
            $setFileValues['id'] = '107';
            $setFileValues['fieldType'] = 'file';
            $setFileValues['name'] = $request->request->get("fileName") ?? "File";
            $setFileValues['placeholder'] = $request->request->get("filePlaceHolder") ?? "File";
            $setFileValues['status'] = $request->request->get('file') == '1' ? '1' : '0';
            $form->setFile(serialize($setFileValues));        
            $em->persist($form);
            $em->flush(); 

            $savedCustomFields = new SavedCustomFields(); 
            $savedCustomFields->setFormId($form->getId());
            $arrayOfIds = []; 

            if(!empty($request->request->get('cf'))) {
                foreach ( $request->request->get('cf') as $cfId) {
                    $arrayOfIds[] = $cfId; 
                }
            } 

            $savedCustomFields->setArrayOfIds(serialize($arrayOfIds)); 
            $em->persist($savedCustomFields);
            $em->flush(); 
            $this->updatedFormField($form->getId(), $request);

            $this->addFlash('success', 'Form successfully created.');
            return new RedirectResponse($this->generateUrl('formbuilder_settings'));
        }

        return $this->render('@_uvdesk_extension_uvdesk_form_component\FormBuilders\manageConfigurations.html.twig', [
                'formbuilder' => [],
                'customfields' => $customFieldsArray ?? null,
        ]);
    }

    public function updateFormConfigurations($id , Request $request)
    {
        $userRole = $this->get('user.service')->getCurrentUser()->getCurrentInstance()->getSupportRole()->getCode();

        if ($this->container->get('user.service')->isAccessAuthorized($userRole)) {
            $em = $this->getDoctrine()->getEntityManager();
            $form =  $em->getRepository('UVDeskFormComponentPackage:Form')->find($id);

            $name = unserialize($form->getName());
            $subject = unserialize($form->getSubject());
            $gdpr = unserialize($form->getGDPR());
            $order_no = unserialize($form->getOrderNo());
            $file = unserialize($form->getFile());
            $type = unserialize($form->getType());
            $email = unserialize($form->getEmail());
            $reply = unserialize($form->getReply());

            $data = [
                'id' => $form->getId(),
                'form_name' => $form->getFormName(),
                'name' => $name['status'],
                'subject' => $subject['status'],
                'gdpr' =>  $gdpr['status'],
                'order_no' => $order_no['status'],
                'file' => $file['status'],
                'type' => $type['status'],
            ];  
            
            $updates_fields = $em->getRepository('UVDeskFormComponentPackage:UpdatedForm')->findBy(array('formId' => $id));

            foreach ($updates_fields as $key => $value) {
                $updateData = [
                            'id'  => $value->getId(),
                         'formId' => $value->getFormId(),
                        'fieldId' => $value->getFieldId(),
                           'name' => $value->getFieldName(),
                    'placeholder' => $value->getPlaceholder(),
                ];

                $updatedFieldData[$key] = $updateData;
            }
          
            $savedCFRepo = $this->getDoctrine()->getRepository(SavedCustomFields::class);
            $savedCF = $savedCFRepo->findOneBy([
                'formId' => $id,
            ]);

            $arrayOfIds = unserialize($savedCF->getArrayOfIds()); 

            $customFields =  $em->getRepository('UVDeskFormComponentPackage:CustomFields')->findAll();
            $customFieldsArray = [];
            $multiOptions = ['select', 'radio', 'checkbox']; 

            foreach ($customFields as $k => $v) {
                $cfRepo = $this->getDoctrine()->getRepository(CustomFieldsValues::class); 
                $cfValues = $cfRepo->findOneBy([
                    'customFields' => $v->getId(),
                ]);

                $temp = [];

                if ($v->getStatus() == true) {
                    if (in_array($v->getId() ,$arrayOfIds)) {
                        $options = [];

                        if (in_array($v->getFieldType(), $multiOptions)) {
                            foreach ($v->getCustomFieldValues() as $cfv) {
                                $options[] = $cfv->getName();
                            }
                            $temp = [
                                'id'        => $v->getId(),
                                'name'      => $v->getName(),
                                'fieldType' => $v->getFieldType(),
                                'value'     => $v->getValue(),
                                'required'  => $v->getRequired(),
                                'agentType' => $v->getAgentType(),
                                'validation'=> json_decode($v->getValidation(), true),
                                'checked'   => true,
                                'options'   => $options,
                            ]; 
                        } else {
                            $temp = [
                                'id'        => $v->getId(),
                                'name'      => $v->getName(),
                                'fieldType' => $v->getFieldType(),
                                'value'     => $v->getValue(),
                                'required'  => $v->getRequired(),
                                'agentType' => $v->getAgentType(),
                                'validation'=> json_decode($v->getValidation(), true),
                                'checked'   => true,
                                'options'   =>  $options,
                            ]; 
                        }
                    }
             
                    $customFieldsArray[$k] = $temp; 
                }
            } 

            foreach($customFieldsArray as &$value1){
                if(!empty($value1)){
                    foreach ($updatedFieldData as $value2) {
                        if($value1['id'] == $value2["fieldId"]){
                            $value1["name"] = $value2["name"];
                            $value1["value"] = $value2["placeholder"];
                        }
                    }
                }
            }

            if ($request->getMethod() == 'POST') {
                $em = $this->getDoctrine()->getEntityManager();
                $form = $em->getRepository('UVDeskFormComponentPackage:Form')->find($id);

                $formName = (empty($request->request->get('form_name')) == true ? "No Form Name Given" : $request->request->get('form_name'));

                $form->setFormName($formName);
                $setEmailValues['id'] = "102";
                $setEmailValues['fieldType'] = "email";
                $setEmailValues['name'] = $email['name'];
                $setEmailValues['placeholder'] = $email['placeholder'];
                $setEmailValues['status'] = '1';
                $form->setEmail(serialize($setEmailValues));
                $setReplyValues['id'] = "105";
                $setReplyValues['fieldType'] = "reply";
                $setReplyValues['name'] = $reply['name'];
                $setReplyValues['placeholder'] = $reply['placeholder'];
                $setReplyValues['status'] = '1';
                $form->setReply(serialize($setReplyValues));
                $setNameValues['id'] = "101";
                $setNameValues['fieldType'] = "name";
                $setNameValues['name'] = $name['name'];
                $setNameValues['placeholder'] = $name['placeholder'];
                $setNameValues['status'] = $request->request->get('name') == '1' ? '1' : '0';
                $form->setName(serialize($setNameValues));
                $setTypeValues['id'] = '104';
                $setTypeValues['fieldType'] = 'type';
                $setTypeValues['name'] = $type['name'];
                $setTypeValues['placeholder'] = $type['placeholder'];
                $setTypeValues['status'] = $request->request->get('type') == '1' ? '1' : '0';
                $form->setType(serialize($setTypeValues));
                $setSubjectValues['id'] = '103';
                $setSubjectValues['fieldType'] = 'subject';
                $setSubjectValues['name'] = $subject['name'];
                $setSubjectValues['placeholder'] = $subject['placeholder'];
                $setSubjectValues['status'] = $request->request->get('subject') == '1' ? '1' : '0';
                $form->setSubject(serialize($setSubjectValues));
                $setGdprValues['id'] = '108';
                $setGdprValues['fieldType'] = 'GDPR';
                $setGdprValues['name'] = $gdpr['name'];
                $setGdprValues['placeholder'] = $gdpr['placeholder'];
                $setGdprValues['status'] = $request->request->get('gdpr') == '1' ? '1' : '0';
                $form->setGDPR(serialize($setGdprValues));
                $setOrderNoValues['id'] = '106';
                $setOrderNoValues['fieldType'] = 'order_no';
                $setOrderNoValues['name'] = $order_no['name'];
                $setOrderNoValues['placeholder'] = $order_no['placeholder'];
                $setOrderNoValues['status'] = $request->request->get('order_no') == '1' ? '1' : '0';
                $form->setOrderNo(serialize($setOrderNoValues));
                $setFileValues['id'] = '107';
                $setFileValues['fieldType'] = 'file';
                $setFileValues['name'] = $file['name'];
                $setFileValues['placeholder'] = $file['placeholder'];
                $setFileValues['status'] = $request->request->get('file') == '1' ? '1' : '0';
                $form->setFile(serialize($setFileValues));
                $em->persist($form);
                $em->flush();

                $savedCFRepo = $this->getDoctrine()->getRepository(SavedCustomFields::class);
                $savedCustomFields = $savedCFRepo->findOneBy([
                    'formId' => $id,
                ]); 

                $arrayOfIds = []; 
                if (!empty($request->request->get('cf'))) {
                    foreach ( $request->request->get('cf') as $cfId) {
                        $arrayOfIds[] = $cfId; 
                    }
                } 

                $savedCustomFields->setArrayOfIds(serialize($arrayOfIds)); 
                $em->persist($savedCustomFields);
                $em->flush();
                $this->updatedFormField($form->getId(), $request); 

                $this->addFlash('success', 'Form successfully updated.');
                return new RedirectResponse($this->generateUrl('formbuilder_settings'));
            }

            return $this->render('@_uvdesk_extension_uvdesk_form_component\FormBuilders\manageConfigurations.html.twig',[
                'formbuilder' => $data ?? null,
                'arrayOfIds' => $arrayOfIds,
                'selectedCustomFields' => $customFieldsArray,
                'customFields' => $customFields,
                'UpdatedFields' => $updatedFieldData,
            ]);
        }
        return $this->render('@UVDeskCoreFramework//dashboard.html.twig', []);
    }

    public function loadFormsXHR(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $records = $em->getRepository("UVDesk\CommunityPackages\UVDesk\FormComponent\Entity\Form")->findAll();
        $arrayOfForms = [];
        foreach($records as $k=>$v)
        {
            $temp = ['id' => $v->getId(),
            'form_name' => $v->getFormName(),
            'name' => $v->getName(),
            'type' => $v->getType(),
            'subject' => $v->getSubject(),
            'gdpr' => $v->getGDPR(),
            'order_no' => $v->getOrderNo(),
            'file' => $v->getFile()];
            $arrayOfForms[$k] = $temp;        
        }
        return new JsonResponse($arrayOfForms); 
    }

    public function removeFormConfiguration($id, Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $form = $em->getRepository('UVDeskFormComponentPackage:Form')->find($id);
        $em->remove($form);
        $em->flush();  

        $updates_fields = $em->getRepository('UVDeskFormComponentPackage:UpdatedForm')->findBy(array('formId' => $id));
    
        foreach ($updates_fields as $updates_field) {
            $em->remove($updates_field);
        }
        $em->flush();

        return new JsonResponse([
            'alertClass' => 'success',
            'alertMessage' => 'Form configuration removed successfully.',
        ]);
    }
 
    public function previewForm($id, Request $request, FileUploadService $fileUploadService , CustomFieldsService $customFieldsService = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $form = $em->getRepository('UVDeskFormComponentPackage:Form')->find($id);
        $referer = $request->server->get('HTTP_REFERER');

        $name = unserialize($form->getName());
        $subject = unserialize($form->getSubject());
        $gdpr = unserialize($form->getGDPR());
        $order_no = unserialize($form->getOrderNo());
        $file = unserialize($form->getFile());
        $type = unserialize($form->getType());
        $email = unserialize($form->getEmail());
        $reply = unserialize($form->getReply());

        $data = [
            'id' => $form->getId(),
            'form_name' => $form->getFormName(),
            'email' => $email['status'],
            'reply' => $reply['status'],
            'name' => $name['status'],
            'subject' => $subject['status'],
            'gdpr' =>  $gdpr['status'],
            'order_no' => $order_no['status'],
            'file' => $file['status'],
            'type' => $type['status'],
        ];

        $updates_fields = $em->getRepository('UVDeskFormComponentPackage:UpdatedForm')->findBy(array('formId' => $id));

        foreach ($updates_fields as $key => $value) {
            $updateData = [
                        'id'  => $value->getId(),
                     'formId' => $value->getFormId(),
                    'fieldId' => $value->getFieldId(),
                       'name' => $value->getFieldName(),
                'placeholder' => $value->getPlaceholder(),
            ];

            $updatedFieldData[$key] = $updateData;
        }

        $savedCFRepo = $this->getDoctrine()->getRepository(SavedCustomFields::class);
        $savedCF = $savedCFRepo->findOneBy([
            'formId' => $id,
        ]);

        $customFieldsArray = [];
        $arrayOfIds = unserialize($savedCF->getArrayOfIds()); 
        $customFields =  $em->getRepository('UVDeskFormComponentPackage:CustomFields')->findAll();

        $multiOptions = ['select', 'radio', 'checkbox']; 
        //preview-original cf array
        foreach ($customFields as $k => $v) {
            $cfRepo = $this->getDoctrine()->getRepository(CustomFieldsValues::class); 
            $cfValues = $cfRepo->findOneBy([
                'customFields' => $v->getId(),
            ]);
            $temp = [];
            
            if ($v->getStatus() == true) {
                if (in_array($v->getId() , $arrayOfIds)) {
                    if (in_array($v->getFieldType(), $multiOptions)) {
                        $options = []; 
                        foreach ($v->getCustomFieldValues() as $cfv) {
                            $options[] =  ['id'=>$cfv->getId(),'name' => $cfv->getName()];  
                        }

                        $temp = [
                            'id'        => $v->getId(),
                            'name'      => $v->getName(),
                            'fieldType' => $v->getFieldType(),
                            'value'     => $v->getValue(),
                            'required'  => $v->getRequired(),
                            'customFieldsDependency' => $v->getCustomFieldsDependency(),
                            'agentType' => $v->getAgentType(),
                            'validation'=> json_decode($v->getValidation(), true),
                            'checked'   => true,
                            'options'   => $options,
                        ]; 
                    } else {
                        $temp = [
                            'id'        => $v->getId(),
                            'name'      => $v->getName(),
                            'fieldType' => $v->getFieldType(),
                            'value'     => $v->getValue(),
                            'required'  => $v->getRequired(),
                            'customFieldsDependency' => $v->getCustomFieldsDependency(),
                            'agentType' => $v->getAgentType(),
                            'validation'=> json_decode($v->getValidation(), true),
                            'checked'   => true,
                        ]; 
                    }
                }
             
                $customFieldsArray[$k] = $temp; 
            } 
        }
       

        foreach($customFieldsArray as &$value1){
            if(!empty($value1)){
                foreach ($updatedFieldData as $value2) {
                    if($value1['id'] == $value2["fieldId"]){
                        $value1["name"] = $value2["name"];
                        $value1["value"] = $value2["placeholder"];
                    }
                }
            }
        }


        if ($request->getMethod() == 'POST') {
            $params = $request->request->all();
            $role = $this->getDoctrine()->getRepository(SupportRole::class)->find(4);
            $attachments[0] = $request->files->get('file');
            $params['from'] = $params['email'];
            $params['subject'] = ( isset($params['subject']) ? $params['subject'] : 'ticket from form: '.$data['form_name']); 
            $params['reply'] = $params['reply'];
            $params['fullname'] = ( isset($params['name']) ? $params['name'] : trim(current(explode('@', $params['email']))));
            $params['createdBy'] = 'customer';
            $params['threadType'] = 'create';
            $params['source'] = 'formbuilder';
            $params['role'] = 4;
            $extra['active'] = 1;
            $userInstance = $this->get('user.service')->createUserInstance($params['email'], $params['fullname'], $role, $extra);
            $params['customer'] = $userInstance;
            $params['user'] = $userInstance; 
            $params['type'] = $this->getDoctrine()->getRepository(TicketType::class)->find(1); 
            $params['message'] = $params['reply']; 
            $params['gdpr'] = ( isset($params['gdpr']) ? $params['gdpr'] : 'null');
            $params['order_no'] = ( isset($params['order_no']) ? $params['order_no'] : 'null');
            $params['attachments']  = empty($attachments[0]) ? null : $attachments;
           
            $thread = $this->get('ticket.service')->createTicketBase($params);
            $ticketId = $thread->getTicket()->getId(); 
            $ticket = $em->getRepository('UVDeskCoreFrameworkBundle:Ticket')->findOneById($ticketId);

            if (!empty($ticket)) {
                $customField = null;
                $ticketCustomFieldValue = null;
    
                if (!empty($request->request->get('customFields'))) {
                    foreach ($request->request->get('customFields') as $key=>$value) {
                        $cf_id = $key;  
                        $cfRepo = $this->getDoctrine()->getRepository(CustomFields::class); 
                        $customField = $cfRepo->findOneBy([
                            'id' => $cf_id,
                        ]);

                        $cfvRepo = $this->getDoctrine()->getRepository(CustomFieldsValues::class); 
                        $cfValues = $cfvRepo->findOneBy([
                            'customFields' => $customField->getId(),
                        ]); 

                        $customField->setValue($value);
                        $ticketCustomFieldValue =  new TicketCustomFieldsValues();
                        $ticketCustomFieldValue->setTicket($ticket); 
                        $ticketCustomFieldValue->setTicketCustomFieldsValues($customField);
                        $ticketCustomFieldValue->setTicketCustomFieldValueValues($cfValues);
                        $ticketCustomFieldValue->setValue($value);
                        $em->persist($ticketCustomFieldValue);
                        $em->persist($ticket);
                        $em->flush(); 
                    } 
                }

                $submittedCustomFieldFiles = $request->files->get('customFields'); 
          
                if (!empty($submittedCustomFieldFiles)) {
                    $baseUploadPath = '/custom-fields/ticket/' . $ticket->getId() . '/';
                    $temporaryFiles = $request->files->get('customFields');
                
                    $uploadedFileCollection = [];
                    foreach($temporaryFiles as $key => $temporaryFile) {
                        $fileName = $fileUploadService->uploadFile($temporaryFile, $baseUploadPath, true);
                        $fileName['key'] = $key;
                        $uploadedFileCollection[] = $fileName;
                    }

                    $cfRepo = $this->getDoctrine()->getRepository(CustomFields::class); 

                    $ticketCustomFieldsValuesCollection = $em->getRepository('UVDeskFormComponentPackage:TicketCustomFieldsValues')->find($ticket);

                    if (!empty($uploadedFileCollection)) {
                        foreach ($uploadedFileCollection as $uploadedFile) {
                            $existingCustomFieldValue = null;
                            if (!empty($ticketCustomFieldsValuesCollection)) {
                                foreach ($ticketCustomFieldsValuesCollection as $ticketCustomField) {
                                    if ($ticketCustomField->getTicketCustomFieldsValues()->getId() == $uploadedFile['key']) {
                                        $existingCustomFieldValue = $ticketCustomField;
                                        break;
                                    }
                                }
                            }

                            $uploadedAttachment = $customFieldsService->addFilesEntryToAttachmentTable([$uploadedFile], $thread);

                            if (!empty($uploadedAttachment[0])) {
                                $customField = $cfRepo->findOneById($uploadedFile['key']);
                                $ticketCustomFieldValue = !empty($existingCustomFieldValue) ? $existingCustomFieldValue : new TicketCustomFieldsValues();
                                
                                $ticketCustomFieldValue->setValue(json_encode(['name' => $uploadedAttachment[0]['name'], 'path' => $uploadedAttachment[0]['path'], 'id' => $uploadedAttachment[0]['id']]));
                                $ticketCustomFieldValue->setTicketCustomFieldsValues($customField);
                                $ticketCustomFieldValue->setTicket($ticket);

                                $em->persist($ticketCustomFieldValue);
                                $em->persist($ticket);
                                $em->flush();
                            }
                        }
                    }
                } //end of file processing
            } // end of !empty ticket

            $this->addFlash('success', 'Ticket Created Successfully!');
            return $this->redirect($referer);
        } 

        return $this->render('@_uvdesk_extension_uvdesk_form_component\FormBuilders\previewForm.html.twig', [
            'formbuilder' => $data,
            'customFields' => $customFieldsArray,
            'UpdatedFields' => $updatedFieldData,
        ]);
    }

    public function previewFormJS($id, Request $request, FileUploadService $fileUploadService , CustomFieldsService $customFieldsService = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $form = $em->getRepository('UVDeskFormComponentPackage:Form')->find($id);
        $referer = $request->server->get('HTTP_REFERER');

        $name = unserialize($form->getName());
        $subject = unserialize($form->getSubject());
        $gdpr = unserialize($form->getGDPR());
        $order_no = unserialize($form->getOrderNo());
        $file = unserialize($form->getFile());
        $type = unserialize($form->getType());
        $email = unserialize($form->getEmail());
        $reply = unserialize($form->getReply());

        $data = [
            'id' => $form->getId(),
            'form_name' => $form->getFormName(),
            'email' => $email['status'],
            'reply' => $reply['status'],
            'name' => $name['status'],
            'subject' => $subject['status'],
            'gdpr' =>  $gdpr['status'],
            'order_no' => $order_no['status'],
            'file' => $file['status'],
            'type' => $type['status'],
        ];

        $updates_fields = $em->getRepository('UVDeskFormComponentPackage:UpdatedForm')->findBy(array('formId' => $id));

        foreach ($updates_fields as $key => $value) {
            $updateData = [
                        'id'  => $value->getId(),
                     'formId' => $value->getFormId(),
                    'fieldId' => $value->getFieldId(),
                       'name' => $value->getFieldName(),
                'placeholder' => $value->getPlaceholder(),
            ];

            $updatedFieldData[$key] = $updateData;
        }

        $savedCFRepo = $this->getDoctrine()->getRepository(SavedCustomFields::class);
        $savedCF = $savedCFRepo->findOneBy([
            'formId' => $id,
        ]);

        $customFieldsArray = [];
        $arrayOfIds = unserialize($savedCF->getArrayOfIds()); 
        $customFields =  $em->getRepository('UVDeskFormComponentPackage:CustomFields')->findAll();

        $multiOptions = ['select', 'radio', 'checkbox']; 
        //preview-original cf array
        foreach ($customFields as $k => $v) {
            $cfRepo = $this->getDoctrine()->getRepository(CustomFieldsValues::class); 
            $cfValues = $cfRepo->findOneBy([
                'customFields' => $v->getId(),
            ]);
            $temp = [];
            
            if ($v->getStatus() == true) {
                if (in_array($v->getId() , $arrayOfIds)) {
                    if (in_array($v->getFieldType(), $multiOptions)) {
                        $options = []; 
                        foreach ($v->getCustomFieldValues() as $cfv) {
                            $options[] =  ['id'=>$cfv->getId(),'name' => $cfv->getName()];  
                        }

                        $temp = [
                            'id'        => $v->getId(),
                            'name'      => $v->getName(),
                            'fieldType' => $v->getFieldType(),
                            'value'     => $v->getValue(),
                            'required'  => $v->getRequired(),
                            'customFieldsDependency' => $v->getCustomFieldsDependency(),
                            'agentType' => $v->getAgentType(),
                            'validation'=> json_decode($v->getValidation(), true),
                            'checked'   => true,
                            'options'   => $options,
                        ]; 
                    } else {
                        $temp = [
                            'id'        => $v->getId(),
                            'name'      => $v->getName(),
                            'fieldType' => $v->getFieldType(),
                            'value'     => $v->getValue(),
                            'required'  => $v->getRequired(),
                            'customFieldsDependency' => $v->getCustomFieldsDependency(),
                            'agentType' => $v->getAgentType(),
                            'validation'=> json_decode($v->getValidation(), true),
                            'checked'   => true,
                        ]; 
                    }
                }
             
                $customFieldsArray[$k] = $temp; 
            } 
        }
       

        foreach($customFieldsArray as &$value1){
            if(!empty($value1)){
                foreach ($updatedFieldData as $value2) {
                    if($value1['id'] == $value2["fieldId"]){
                        $value1["name"] = $value2["name"];
                        $value1["value"] = $value2["placeholder"];
                    }
                }
            }
        }

        if ($request->getMethod() == 'POST') {
            $params = $request->request->all();
            $role = $this->getDoctrine()->getRepository(SupportRole::class)->find(4);
            $attachments[0] = $request->files->get('file');
            $params['from'] = $params['email'];
            $params['subject'] = ( isset($params['subject']) ? $params['subject'] : 'ticket from form: '.$data['form_name']); 
            $params['reply'] = $params['reply'];
            $params['fullname'] = ( isset($params['name']) ? $params['name'] : trim(current(explode('@', $params['email']))));
            $params['createdBy'] = 'customer';
            $params['threadType'] = 'create';
            $params['source'] = 'formbuilder';
            $params['role'] = 4;
            $extra['active'] = 1;
            $userInstance = $this->get('user.service')->createUserInstance($params['email'], $params['fullname'], $role, $extra);
            $params['customer'] = $userInstance;
            $params['user'] = $userInstance; 
            $params['type'] = $this->getDoctrine()->getRepository(TicketType::class)->find(1); 
            $params['message'] = $params['reply']; 
            $params['gdpr'] = ( isset($params['gdpr']) ? $params['gdpr'] : 'null');
            $params['order_no'] = ( isset($params['order_no']) ? $params['order_no'] : 'null');
            $params['attachments']  = empty($attachments[0]) ? null : $attachments;
           
            $thread = $this->get('ticket.service')->createTicketBase($params);
            $ticketId = $thread->getTicket()->getId(); 
            $ticket = $em->getRepository('UVDeskCoreFrameworkBundle:Ticket')->findOneById($ticketId);

            if (!empty($ticket)) {
                $customField = null;
                $ticketCustomFieldValue = null;
    
                if (!empty($request->request->get('customFields'))) {
                    foreach ($request->request->get('customFields') as $key=>$value) {
                        $cf_id = $key;  
                        $cfRepo = $this->getDoctrine()->getRepository(CustomFields::class); 
                        $customField = $cfRepo->findOneBy([
                            'id' => $cf_id,
                        ]);

                        $cfvRepo = $this->getDoctrine()->getRepository(CustomFieldsValues::class); 
                        $cfValues = $cfvRepo->findOneBy([
                            'customFields' => $customField->getId(),
                        ]); 

                        $customField->setValue($value);
                        $ticketCustomFieldValue =  new TicketCustomFieldsValues();
                        $ticketCustomFieldValue->setTicket($ticket); 
                        $ticketCustomFieldValue->setTicketCustomFieldsValues($customField);
                        $ticketCustomFieldValue->setTicketCustomFieldValueValues($cfValues);
                        $ticketCustomFieldValue->setValue($value);
                        $em->persist($ticketCustomFieldValue);
                        $em->persist($ticket);
                        $em->flush(); 
                    } 
                }

                $submittedCustomFieldFiles = $request->files->get('customFields'); 
          
                if (!empty($submittedCustomFieldFiles)) {
                    $baseUploadPath = '/custom-fields/ticket/' . $ticket->getId() . '/';
                    $temporaryFiles = $request->files->get('customFields');
                
                    $uploadedFileCollection = [];
                    foreach($temporaryFiles as $key => $temporaryFile) {
                        $fileName = $fileUploadService->uploadFile($temporaryFile, $baseUploadPath, true);
                        $fileName['key'] = $key;
                        $uploadedFileCollection[] = $fileName;
                    }

                    $cfRepo = $this->getDoctrine()->getRepository(CustomFields::class); 

                    $ticketCustomFieldsValuesCollection = $em->getRepository('UVDeskFormComponentPackage:TicketCustomFieldsValues')->find($ticket);

                    if (!empty($uploadedFileCollection)) {
                        foreach ($uploadedFileCollection as $uploadedFile) {
                            $existingCustomFieldValue = null;
                            if (!empty($ticketCustomFieldsValuesCollection)) {
                                foreach ($ticketCustomFieldsValuesCollection as $ticketCustomField) {
                                    if ($ticketCustomField->getTicketCustomFieldsValues()->getId() == $uploadedFile['key']) {
                                        $existingCustomFieldValue = $ticketCustomField;
                                        break;
                                    }
                                }
                            }

                            $uploadedAttachment = $customFieldsService->addFilesEntryToAttachmentTable([$uploadedFile], $thread);

                            if (!empty($uploadedAttachment[0])) {
                                $customField = $cfRepo->findOneById($uploadedFile['key']);
                                $ticketCustomFieldValue = !empty($existingCustomFieldValue) ? $existingCustomFieldValue : new TicketCustomFieldsValues();
                                
                                $ticketCustomFieldValue->setValue(json_encode(['name' => $uploadedAttachment[0]['name'], 'path' => $uploadedAttachment[0]['path'], 'id' => $uploadedAttachment[0]['id']]));
                                $ticketCustomFieldValue->setTicketCustomFieldsValues($customField);
                                $ticketCustomFieldValue->setTicket($ticket);

                                $em->persist($ticketCustomFieldValue);
                                $em->persist($ticket);
                                $em->flush();
                            }
                        }
                    }
                } //end of file processing
            } // end of !empty ticket

            $this->addFlash('success', 'Ticket Created Successfully!');
            return $this->redirect($referer);
        } 

        return $this->render('@_uvdesk_extension_uvdesk_form_component\FormBuilders\previewFormJS.html.twig', [
            'formbuilder' => $data,
            'customFields' => $customFieldsArray,
            'UpdatedFields' => $updatedFieldData,
        ]);
    }

    public function settingsFormConfigurations($id , Request $request)
    {
        $userRole = $this->get('user.service')->getCurrentUser()->getCurrentInstance()->getSupportRole()->getCode();
        $em = $this->getDoctrine()->getEntityManager();
        $form = $em->getRepository('UVDeskFormComponentPackage:Form')->find($id);
        $get_updated_fields = $em->getRepository('UVDeskFormComponentPackage:UpdatedForm')->findBy(array('formId' => $id));

        if ($this->container->get('user.service')->isAccessAuthorized($userRole)) {
            $name = unserialize($form->getName());
            $subject = unserialize($form->getSubject());
            $gdpr = unserialize($form->getGDPR());
            $order_no = unserialize($form->getOrderNo());
            $file = unserialize($form->getFile());
            $type = unserialize($form->getType());
            $email = unserialize($form->getEmail());
            $reply = unserialize($form->getReply());
            
            $data = [
                'id' => $form->getId(),
                'name' => $name,
                'subject' => $subject,
                'gdpr' =>  $gdpr,
                'order_no' => $order_no,
                'file' => $file,
                'type' => $type,
            ];

            foreach ($get_updated_fields as $key => $value) {

                $upd = [
                    'id'        => $value->getId(),
                    'formId'      => $value->getFormId(),
                    'fieldId' => $value->getFieldId(),
                    'name'  => $value->getFieldName(),
                    'placeholder'  => $value->getPlaceholder(),
                ];

                $updatedFields[$key] =  $upd;
            }

            $savedCFRepo = $this->getDoctrine()->getRepository(SavedCustomFields::class);
            $savedCF = $savedCFRepo->findOneBy([
                'formId' => $id,
            ]);

            $arrayOfIds = unserialize($savedCF->getArrayOfIds()); 

            $customFields =  $em->getRepository('UVDeskFormComponentPackage:CustomFields')->findAll();
            $customFieldsArray = [];
            $multiOptions = ['select', 'radio', 'checkbox']; 

            foreach ($customFields as $k => $v) {
                $cfRepo = $this->getDoctrine()->getRepository(CustomFieldsValues::class); 
                $cfValues = $cfRepo->findOneBy([
                    'customFields' => $v->getId(),
                ]);

                $temp = [];

                if ($v->getStatus() == true) {
                    if (in_array($v->getId() ,$arrayOfIds)) {
                        $options = [];

                        if (in_array($v->getFieldType(), $multiOptions)) {
                            foreach ($v->getCustomFieldValues() as $cfv) {
                                $options[] = $cfv->getName();
                            }
                            $temp = [
                                'id'        => $v->getId(),
                                'name'      => $v->getName(),
                                'fieldType' => $v->getFieldType(),
                                'value'     => $v->getValue(),
                                'required'  => $v->getRequired(),
                                'agentType' => $v->getAgentType(),
                                'validation'=> json_decode($v->getValidation(), true),
                                'checked'   => true,
                                'options'   => $options,
                            ]; 
                        } else {
                            $temp = [
                                'id'        => $v->getId(),
                                'name'      => $v->getName(),
                                'fieldType' => $v->getFieldType(),
                                'value'     => $v->getValue(),
                                'required'  => $v->getRequired(),
                                'agentType' => $v->getAgentType(),
                                'validation'=> json_decode($v->getValidation(), true),
                                'checked'   => true,
                                'options'   =>  $options,
                            ]; 
                        }
                    }
             
                    $customFieldsArray[$k] = $temp; 
                }
            } 
        
            return $this->render('@_uvdesk_extension_uvdesk_form_component\FormBuilders\settings.html.twig',[
                'updatedformfields' => $updatedFields,
                'formbuilder' => $data,
                'customFields' => $customFieldsArray,
            ]);
        }
        return $this->render('@UVDeskCoreFramework//dashboard.html.twig', []);
    }
    
   
    public function submitFormFieldXHR( Request $request)
    {
        $id = $request->request->get('hash');
        $fieldTypeid = $request->request->get('list_id');
        $fieldTypeName = $request->request->get('list_name');
        $em = $this->getDoctrine()->getEntityManager();

        if(!empty($fieldTypeid)){
            $updatedform = $em->getRepository('UVDeskFormComponentPackage:UpdatedForm')->findOneBy(array(
                'fieldId'=> $fieldTypeid,
                'formId'=> $id,
            ));
            
            if (!empty($updatedform)){
                $updatedform->setFieldName($request->request->get($fieldTypeName.'-name'));
                $updatedform->setPlaceholder($request->request->get($fieldTypeName.'-placeholder'));
           
                $em->persist($updatedform);
                $em->flush();
            }
        }
        
        return new RedirectResponse($this->generateUrl('formbuilder_settings'));
    }

    public function updatedFormField($id, Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $userRole = $this->get('user.service')->getCurrentUser()->getCurrentInstance()->getSupportRole()->getCode();
        $form = $em->getRepository('UVDeskFormComponentPackage:Form')->find($id);

        if ($this->container->get('user.service')->isAccessAuthorized($userRole)) {
                $updatedform = new UpdatedForm();
                $formFieldsArray = [
                    'email' => unserialize($form->getEmail()),
                    'reply' => unserialize($form->getReply()),
                    'name' => unserialize($form->getName()),
                    'type' => unserialize($form->getType()),
                    'subject' => unserialize($form->getSubject()),
                    'gdpr' =>  unserialize($form->getGDPR()),
                    'order_no' => unserialize($form->getOrderNo()),
                    'file' => unserialize($form->getFile()),
                ];

                foreach ($formFieldsArray as  $value) {
                    $updateform = $em->getRepository('UVDeskFormComponentPackage:UpdatedForm')->findOneBy(array(
                        'fieldId'=> $value['id'],
                        'formId'=> $id,
                    ));
                
                    if(empty($updateform)){
                        if ($value['status'] == "1") {
                            $updatedform = new UpdatedForm();
                            $updatedform->setFormId($id);
                            $updatedform->setFieldId($value['id']); 
                            $updatedform->setFieldName($value['name']);
                            $updatedform->setPlaceholder($value['placeholder']);
                
                            $em->persist($updatedform);
                            $em->flush();
                        } 
                    } 
                }

                $savedCFRepo = $this->getDoctrine()->getRepository(SavedCustomFields::class);
                $savedCF = $savedCFRepo->findOneBy([
                    'formId' => $id,
                ]);

                $arrayOfIds = unserialize($savedCF->getArrayOfIds()); 

                $customFields =  $em->getRepository('UVDeskFormComponentPackage:CustomFields')->findAll();
                $customFieldsArray = [];
                $multiOptions = ['select', 'radio', 'checkbox']; 

                foreach ($customFields as $k => $v) {
                    $cfRepo = $this->getDoctrine()->getRepository(CustomFieldsValues::class); 
                    $cfValues = $cfRepo->findOneBy([
                        'customFields' => $v->getId(),
                    ]);

                    $temp = [];

                    if ($v->getStatus() == true) {
                        if (in_array($v->getId() ,$arrayOfIds)) {
                            $options = [];

                            if (in_array($v->getFieldType(), $multiOptions)) {
                            
                                $temp = [
                                    'id'        => $v->getId(),
                                    'name'      => $v->getName(),
                                    'value'     => $v->getValue(),
                                ]; 
                            } else {
                                $temp = [
                                    'id'        => $v->getId(),
                                    'name'      => $v->getName(),
                                    'value'     => $v->getValue(),
                                ]; 
                            }
                        }
             
                        $customFieldsArray[$k] = $temp; 
                    }
                } 

                foreach ($customFieldsArray as  $value) {
                    if(!empty($value)) {
                        $updateform = $em->getRepository('UVDeskFormComponentPackage:UpdatedForm')->findOneBy(array(
                            'fieldId'=> $value['id'],
                            'formId'=> $id,
                        ));
        
                        if (empty($updateform)) {
                            if(empty($value['value'])) {
                                $value['value'] = $value['name'];
                            }
                                
                            $updatedform = new UpdatedForm();
                            $updatedform->setFormId($id);
                            $updatedform->setFieldId($value['id']); 
                            $updatedform->setFieldName($value['name']);
                            $updatedform->setPlaceholder($value['value']);
                
                            $em->persist($updatedform);
                            $em->flush();
                        }
                    }
                }
        }
    }
}