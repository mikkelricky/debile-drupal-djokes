import React from 'react'
import { CloseButton, Form, Modal } from 'react-bootstrap'

const Settings = ({ closeSettings = () => {}, alwaysShowPunchline, setAlwaysShowPunchline = () => {} }) => {
  return (
    <Modal show onHide={closeSettings} backdrop='static' keyboard={false}>
      <Modal.Header>
        <Modal.Title>Settings</Modal.Title>
        <CloseButton aria-label='Hide' onClick={closeSettings} />
      </Modal.Header>

      <Modal.Body>
        <Form>
          <Form.Group controlId='alwaysShowPunchline'>
            <Form.Check type='checkbox' label='Always show punchline' checked={alwaysShowPunchline} onChange={(event) => setAlwaysShowPunchline(event.target.checked)} />
          </Form.Group>
        </Form>
      </Modal.Body>
    </Modal>
  )
}

export default Settings
