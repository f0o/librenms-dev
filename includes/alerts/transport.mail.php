$data = $this->mail();
return send_mail($data['emails'], $data['subject'], $data['body']);