import React from 'react'
import { CloseButton, Form, Modal } from 'react-bootstrap'
import { useTranslation } from 'react-i18next'

const Settings = ({ closeSettings = () => {}, alwaysShowPunchline, setAlwaysShowPunchline = () => {} }) => {
  const { t } = useTranslation()

  return (
    <Modal show onHide={closeSettings} backdrop='static' keyboard={false}>
      <Modal.Header>
        <Modal.Title>{t('Settings')}</Modal.Title>
        <CloseButton aria-label='Hide' onClick={closeSettings} />
      </Modal.Header>

      <Modal.Body>
        <Form>
          <Form.Group controlId='alwaysShowPunchline'>
            <Form.Check type='checkbox' label={t('Always show punchline')} checked={alwaysShowPunchline} onChange={(event) => setAlwaysShowPunchline(event.target.checked)} />
          </Form.Group>
        </Form>
      </Modal.Body>
    </Modal>
  )
}

export default Settings
